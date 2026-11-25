<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\AppointmentTypes;
use App\Models\DoctorAppointmentTypes;
use App\Models\MedicalReport;
use App\Models\DoctorProfile;
use App\Models\Specialities;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Read-only presentation endpoints; all mutations use existing controllers. */
class WorkspaceController extends Controller
{
    public function dashboard(Request $request)
    {
        $visits = Appointments::where('patient_id', $request->user()->id);
        $upcomingCount = (clone $visits)->whereIn('status', ['pending', 'confirmed'])->where('scheduled_at', '>', now())->count();
        $completedCount = (clone $visits)->where('status', 'completed')->count();
        $reportsCount = MedicalReport::where('status', 'approved')->whereRelation('medical_note.appointment', 'patient_id', $request->user()->id)->count();
        $next = (clone $visits)->with('doctor_appointment_type.appointment_type', 'doctor_appointment_type.doctor_profile.user')
            ->whereIn('status', ['pending', 'confirmed'])->where('scheduled_at', '>', now())->orderBy('scheduled_at')->first();
        return response()->view('patient.dashboard', compact('upcomingCount', 'completedCount', 'reportsCount', 'next'))->header('Cache-Control', 'private, no-store');
    }

    public function directory(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:100', 'speciality' => 'nullable|integer']);
        $doctors = DoctorProfile::with('user', 'speciality')->where('is_active', true)
            ->whereHas('user', fn ($q) => $q->where('is_active', true)->when($filters['q'] ?? null, fn ($q, $name) => $q->where('name', 'like', '%'.$name.'%')))
            ->when($filters['speciality'] ?? null, fn ($q, $id) => $q->where('speciality_id', $id))
            ->paginate(12)->withQueryString();
        return view('public.dentists', ['doctors' => $doctors, 'specialities' => Specialities::orderBy('name')->get()]);
    }

    public function dentist(int $id)
    {
        $doctor = DoctorProfile::with('user', 'speciality', 'doctor_appointment_types.appointment_type', 'doctor_schedules')
            ->where('is_active', true)->whereHas('user', fn ($q) => $q->where('is_active', true))->findOrFail($id);
        return view('public.dentist', compact('doctor'));
    }

    public function booking(Request $request)
    {
        $data = $request->validate(['service' => 'nullable|integer|exists:doctor_appointment_types,id', 'date' => 'nullable|date_format:Y-m-d|after_or_equal:today']);
        $services = DoctorAppointmentTypes::with('appointment_type', 'doctor_profile.user')
            ->whereHas('doctor_profile', fn ($q) => $q->where('is_active', true)->whereHas('user', fn ($q) => $q->where('is_active', true)))
            ->where('duration_minutes', '>', 0)->get();
        $service = $services->firstWhere('id', $data['service'] ?? null);
        $date = $data['date'] ?? now()->toDateString();
        $slots = [];
        if ($service) {
            $day = Carbon::parse($date);
            $schedules = $service->doctor_profile->doctor_schedules()->where('is_available', true)->where('day_of_week', $day->dayOfWeek)->get();
            $bookings = Appointments::with('doctor_appointment_type')->whereRelation('doctor_appointment_type', 'doctor_profile_id', $service->doctor_profile_id)
                ->whereIn('status', ['pending', 'confirmed'])->whereDate('scheduled_at', $date)->get();
            foreach ($schedules as $schedule) {
                $start = Carbon::parse($date.' '.$schedule->start_time);
                $close = Carbon::parse($date.' '.$schedule->end_time);
                for ($time = $start->copy(); $time->copy()->addMinutes($service->duration_minutes)->lte($close); $time->addMinutes(15)) {
                    $end = $time->copy()->addMinutes($service->duration_minutes);
                    if (!$time->isFuture() || !$time->isSameDay($end)) continue;
                    $overlap = $bookings->contains(fn ($visit) => $visit->scheduled_at->lt($end) && $visit->scheduled_at->copy()->addMinutes($visit->doctor_appointment_type->duration_minutes)->gt($time));
                    if (!$overlap) $slots[$time->format('Y-m-d\TH:i')] = $time->format('H:i');
                }
            }
            ksort($slots);
        }
        return view('patient.booking', compact('services', 'service', 'date', 'slots'));
    }

    public function reviewBooking(Request $request)
    {
        $data = $request->validate(['doctor_appointment_type_id' => 'required|integer|exists:doctor_appointment_types,id', 'scheduled_at' => 'required|date|after:now', 'reason' => 'nullable|string|max:1000']);
        $service = DoctorAppointmentTypes::with('doctor_profile.user', 'appointment_type')->findOrFail($data['doctor_appointment_type_id']);
        return view('patient.booking-review', compact('data', 'service'));
    }

    public function detail(Request $request, int $id)
    {
        $appointment = Appointments::with('doctor_appointment_type.doctor_profile.user', 'doctor_appointment_type.appointment_type')
            ->where('patient_id', $request->user()->id)->findOrFail($id);
        return response()->view('patient.appointment', compact('appointment'))->header('Cache-Control', 'private, no-store');
    }
    public function patient(Request $request)
    {
        $appointments = Appointments::with('doctor_appointment_type.appointment_type', 'doctor_appointment_type.doctor_profile.user')
            ->where('patient_id', $request->user()->id)->orderByDesc('scheduled_at')->paginate(15)
            ->appends($request->only('reports_page'));
        $services = DoctorAppointmentTypes::with('appointment_type', 'doctor_profile.user', 'doctor_profile.doctor_schedules')
            ->whereHas('doctor_profile', fn ($query) => $query->where('is_active', true)->whereHas('user', fn ($q) => $q->where('is_active', true)))->get();
        $reports = MedicalReport::where('status', 'approved')
            ->whereRelation('medical_note.appointment', 'patient_id', $request->user()->id)
            ->latest()
            ->orderByDesc('id')
            ->paginate(5, pageName: 'reports_page')
            ->appends($request->only('page'))
            ->fragment('reports-title');

        return response()->view('patient.workspace', compact('appointments', 'services', 'reports'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function doctor(Request $request)
    {
        $doctor = $request->user()->doctor_profile;
        abort_unless($doctor, 403);
        $appointments = Appointments::with('user', 'doctor_appointment_type.appointment_type')
            ->whereRelation('doctor_appointment_type', 'doctor_profile_id', $doctor->id)
            ->orderByDesc('scheduled_at')->paginate(20);

        return response()->view('doctor.appointments', compact('appointments'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function types()
    {
        return view('admin.appointment-types', ['types' => AppointmentTypes::orderBy('name')->paginate(20)]);
    }
}
