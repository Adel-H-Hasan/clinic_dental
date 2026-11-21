<?php

namespace App\Http\Controllers;

use App\Models\AppointmentTypes;
use Illuminate\Http\Request;

class DoctorAppointmentTypesController extends Controller
{
    public function index(Request $request)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $services = $doctor->doctor_appointment_types()
            ->with('appointment_type')
            ->get();

        $appointmentTypes = AppointmentTypes::orderBy('name')
            ->whereNotIn('id', $services->pluck('appointment_type_id'))
            ->get();

        return view('doctor.services.index', [
            'services' => $services,
            'appointmentTypes' => $appointmentTypes,
        ]);
    }

    public function store(Request $request)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $data = $request->validate([
            'appointment_type_id' => 'required|integer|exists:appointment_types,id',
            'duration_minutes' => 'required|integer|min:1|max:2147483647',
            'price' => 'required|numeric|decimal:0,2|min:0|max:999999.99',
        ]);

        $service = $doctor->doctor_appointment_types()->firstOrCreate(
            ['appointment_type_id' => $data['appointment_type_id']],
            [
                'duration_minutes' => $data['duration_minutes'],
                'price' => $data['price'],
            ]
        );

        if (!$service->wasRecentlyCreated) {
            return back()
                ->withErrors(['appointment_type_id' => 'You already offer this service.'])
                ->withInput();
        }

        return redirect()->route('doctor.services.index');
    }

    public function edit(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $service = $doctor->doctor_appointment_types()->find($id);

        if ($service === null) {
            abort(404);
        }

        return view('doctor.services.edit', ['service' => $service]);
    }

    public function update(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $service = $doctor->doctor_appointment_types()->find($id);

        if ($service === null) {
            abort(404);
        }

        $data = $request->validate([
            'duration_minutes' => 'required|integer|min:1|max:2147483647',
            'price' => 'required|numeric|decimal:0,2|min:0|max:999999.99',
        ]);

        if ((int) $data['duration_minutes'] !== (int) $service->duration_minutes) {
            $hasUpcomingBookings = $service->appointments()
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('scheduled_at', '>=', now())
                ->exists();

            if ($hasUpcomingBookings) {
                return back()
                    ->withErrors(['duration_minutes' => 'This service has upcoming bookings.'])
                    ->withInput();
            }
        }

        $service->duration_minutes = $data['duration_minutes'];
        $service->price = $data['price'];
        $service->save();

        return redirect()->route('doctor.services.index');
    }

    public function destroy(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $service = $doctor->doctor_appointment_types()->find($id);

        if ($service === null) {
            abort(404);
        }

        if ($service->appointments()->exists()) {
            return back()->withErrors([
                'service' => 'This service has appointments and cannot be deleted.',
            ]);
        }

        $service->delete();

        return redirect()->route('doctor.services.index');
    }
}
