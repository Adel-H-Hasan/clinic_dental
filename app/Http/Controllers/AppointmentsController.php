<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\DoctorAppointmentTypes;
use App\Models\DoctorProfile;
use App\Models\DoctorSchedules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $appointments = Appointments::where(
            'patient_id',
            $request->user()->id
        )
            ->orderBy('scheduled_at', 'asc')
            ->get();
        return response()->json($appointments);
    }


    public function show(Request $request, int $id)
    {
        $appointment = Appointments::with([
            'doctor_appointment_type.appointment_type',
            'doctor_appointment_type.doctor_profile',
        ])
            ->where('patient_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($appointment);
    }


    public function store(Request $request)
    {
        // 1. Validate the booking request
        $validated = $request->validate([
            'doctor_appointment_type_id' => [
                'required',
                'integer',
                'exists:doctor_appointment_types,id',
            ],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
            ],
            'reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        // 2. Get the service and calculate booking time
        $service = DoctorAppointmentTypes::findOrFail(
            $validated['doctor_appointment_type_id']
        );

        $start = Carbon::parse($validated['scheduled_at']);


        // 3. Execute booking checks and save atomically
        return DB::transaction(function () use (
            $request,
            $validated,
            $service,
            $start
        ) {
            // Lock this doctor while checking and booking
            DoctorProfile::whereKey($service->doctor_profile_id)
                ->lockForUpdate()
                ->firstOrFail();


            // Read the latest service data after locking the doctor
            $service = DoctorAppointmentTypes::whereKey($service->id)
                ->where('doctor_profile_id', $service->doctor_profile_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($service->duration_minutes <= 0) {
                throw ValidationException::withMessages([
                    'doctor_appointment_type_id' => 'Invalid service duration.',
                ]);
            }

            // Make sure the requested time is still in the future
            if (!$start->isFuture()) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Appointment time must be in the future.',
                ]);
            }

            // Calculate the appointment end using the latest duration
            $end = $start->copy()->addMinutes(
                $service->duration_minutes
            );

            if (!$start->isSameDay($end)) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Appointment must end on the same day.',
                ]);
            }

            // 4. Check doctor's working hours
            $doctorAvailable = DoctorSchedules::where(
                'doctor_profile_id',
                $service->doctor_profile_id
            )
                ->where('day_of_week', $start->dayOfWeek)
                ->where('is_available', true)
                ->where('start_time', '<=', $start->format('H:i:s'))
                ->where('end_time', '>=', $end->format('H:i:s'))
                ->exists();

            if (!$doctorAvailable) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Doctor is not available at this time.',
                ]);
            }

            // 5. Check overlapping appointments
            $hasOverlap = DB::table('appointments')
                ->join(
                    'doctor_appointment_types as existing_service',
                    'appointments.doctor_appointment_type_id',
                    '=',
                    'existing_service.id'
                )
                ->where(
                    'existing_service.doctor_profile_id',
                    $service->doctor_profile_id
                )
                ->whereIn('appointments.status', [
                    'pending',
                    'confirmed',
                ])
                ->where(
                    'appointments.scheduled_at',
                    '<',
                    $end->format('Y-m-d H:i:s')
                )
                ->whereRaw(
                    'DATE_ADD(appointments.scheduled_at, INTERVAL existing_service.duration_minutes MINUTE) > ?',
                    [$start->format('Y-m-d H:i:s')]
                )
                ->exists();

            if ($hasOverlap) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Doctor already has an appointment at this time.',
                ]);
            }

            // 6. Save the appointment

            $appointment = Appointments::create([
                'patient_id' => $request->user()->id,
                'doctor_appointment_type_id' => $service->id,
                'scheduled_at' => $start,
                'status' => 'pending',
                'reason' => $validated['reason'] ?? null,
            ]);

            return back()->with(
                'success',
                'Appointment booked successfully.'
            );
        }, 3);
    }


    public function cancel(Request $request, int $id)
    {
        // 1. Find the patient's appointment
        $existing = Appointments::where(
            'patient_id',
            $request->user()->id
        )->findOrFail($id);

        // 2. Find the doctor for this appointment
        $service = DoctorAppointmentTypes::findOrFail(
            $existing->doctor_appointment_type_id
        );

        $doctorId = $service->doctor_profile_id;

        // 3. Lock the doctor and cancel safely
        return DB::transaction(function () use (
            $request,
            $id,
            $doctorId
        ) {
            DoctorProfile::whereKey($doctorId)
                ->lockForUpdate()
                ->firstOrFail();

            // Read the appointment again after getting the lock
            $appointment = Appointments::where(
                'patient_id',
                $request->user()->id
            )->findOrFail($id);

            if (!in_array($appointment->status, ['pending', 'confirmed'], true)) {
                throw ValidationException::withMessages([
                    'appointment' => 'This appointment cannot be cancelled.',
                ]);
            }

            if (!Carbon::parse($appointment->scheduled_at)->isFuture()) {
                throw ValidationException::withMessages([
                    'appointment' => 'Past appointments cannot be cancelled.',
                ]);
            }

            $appointment->update([
                'status' => 'cancelled',
            ]);

            return back()->with(
                'success',
                'Appointment cancelled successfully.'
            );
        }, 3);
    }

    public function reschedule(Request $request, int $id)
    {
        // 1. Validate the new appointment date
        $validated = $request->validate([
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
            ],
        ]);

        // 2. Find the patient's appointment
        $appointment = Appointments::where(
            'patient_id',
            $request->user()->id
        )->findOrFail($id);

        // 3. Check if rescheduling is allowed
        if (!in_array($appointment->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'appointment' => 'This appointment cannot be rescheduled.',
            ]);
        }

        if (!Carbon::parse($appointment->scheduled_at)->isFuture()) {
            throw ValidationException::withMessages([
                'appointment' => 'Past appointments cannot be rescheduled.',
            ]);
        }


        // 4. Get the service and calculate the new end time
        $service = DoctorAppointmentTypes::findOrFail(
            $appointment->doctor_appointment_type_id
        );

        $start = Carbon::parse($validated['scheduled_at']);
        // 5. Check and update inside a transaction
        return DB::transaction(function () use (
            $request,
            $id,
            $service,
            $start,

        ) {
            $doctorId = $service->doctor_profile_id;

            DoctorProfile::whereKey($doctorId)
                ->lockForUpdate()
                ->firstOrFail();

            // Recheck the appointment after locking the doctor
            $appointment = Appointments::where(
                'patient_id',
                $request->user()->id
            )->findOrFail($id);

            if (
                !in_array($appointment->status, ['pending', 'confirmed'], true)
                || !Carbon::parse($appointment->scheduled_at)->isFuture()
                || !$start->isFuture()
            ) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'This appointment cannot be rescheduled.',
                ]);
            }


            $service = DoctorAppointmentTypes::whereKey(
                $appointment->doctor_appointment_type_id
            )
                ->where('doctor_profile_id', $doctorId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($service->duration_minutes <= 0) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Invalid service duration.',
                ]);
            }

            $end = $start->copy()->addMinutes(
                $service->duration_minutes
            );

            if (!$start->isSameDay($end)) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Appointment must end on the same day.',
                ]);
            }


            // 6. Check the doctor's working hours
            $doctorAvailable = DoctorSchedules::where('doctor_profile_id', $doctorId)
                ->where('day_of_week', $start->dayOfWeek)
                ->where('is_available', true)
                ->where('start_time', '<=', $start->format('H:i:s'))
                ->where('end_time', '>=', $end->format('H:i:s'))
                ->exists();

            if (!$doctorAvailable) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Doctor is not available at this time.',
                ]);
            }

            // 7. Check overlapping bookings, excluding this appointment
            $hasOverlap = DB::table('appointments')
                ->join(
                    'doctor_appointment_types as existing_service',
                    'appointments.doctor_appointment_type_id',
                    '=',
                    'existing_service.id'
                )
                ->where('existing_service.doctor_profile_id', $doctorId)
                ->where('appointments.id', '!=', $appointment->id)
                ->whereIn('appointments.status', ['pending', 'confirmed'])
                ->where('appointments.scheduled_at', '<', $end->format('Y-m-d H:i:s'))
                ->whereRaw(
                    'DATE_ADD(appointments.scheduled_at, INTERVAL existing_service.duration_minutes MINUTE) > ?',
                    [$start->format('Y-m-d H:i:s')]
                )
                ->exists();

            if ($hasOverlap) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Doctor already has an appointment at this time.',
                ]);
            }

            // 8. Update the existing appointment
            $appointment->update([
                'scheduled_at' => $start,
            ]);

            return back()->with(
                'success',
                'Appointment rescheduled successfully.'
            );
        }, 3);
    }


    public function doctorIndex(Request $request)
    {
        // Get the logged-in doctor's profile
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403, 'A doctor profile is required.');
        }

        // Get appointments belonging to this doctor
        $appointments = Appointments::with([
            'user',
            'doctor_appointment_type.appointment_type',
        ])
            ->whereHas('doctor_appointment_type', function ($query) use ($doctor) {
                $query->where('doctor_profile_id', $doctor->id);
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();
        return response()->json($appointments);
    }


    public function updateStatus(Request $request, int $id)
    {
        // 1. Validate the requested status
        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed',
        ]);

        // 2. Get the logged-in doctor's profile
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403, 'A doctor profile is required.');
        }

        // 3. Update safely inside a transaction
        return DB::transaction(function () use ($doctor, $id, $validated) {

            DoctorProfile::whereKey($doctor->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Find an appointment belonging to this doctor
            $appointment = Appointments::whereHas(
                'doctor_appointment_type',
                function ($query) use ($doctor) {
                    $query->where('doctor_profile_id', $doctor->id);
                }
            )->findOrFail($id);

            // 4. Validate the status transition
            $canConfirm = $appointment->status === 'pending'
                && $validated['status'] === 'confirmed'
                && Carbon::parse($appointment->scheduled_at)->isFuture();


            $duration = $appointment->doctor_appointment_type->duration_minutes;

            $appointmentEnd = Carbon::parse($appointment->scheduled_at)
                ->addMinutes($duration);

            $canComplete = $appointment->status === 'confirmed'
                && $validated['status'] === 'completed'
                && $appointmentEnd->lessThanOrEqualTo(now());


            if (!$canConfirm && !$canComplete) {
                throw ValidationException::withMessages([
                    'status' => 'This status change is not allowed.',
                ]);
            }

            // 5. Save the new status
            $appointment->update([
                'status' => $validated['status'],
            ]);

            return back()->with(
                'success',
                'Appointment status updated successfully.'
            );
        }, 3);
    }
}
