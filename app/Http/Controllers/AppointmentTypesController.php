<?php

namespace App\Http\Controllers;

use App\Models\AppointmentTypes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentTypesController extends Controller
{
    public function index()
    {
        $types = AppointmentTypes::all();

        return $types;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:appointment_types,name',
            'description' => 'nullable|string',
        ]);

        AppointmentTypes::create($validated);

        return back()->with(
            'success',
            'Appointment type created successfully.'
        );
    }

    public function update(Request $request, AppointmentTypes $appointmentType)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('appointment_types', 'name')
                    ->ignore($appointmentType->id),
            ],
            'description' => 'nullable|string',
        ]);

        $appointmentType->update($validated);

        return back()->with(
            'success',
            'Appointment type updated successfully.'
        );
    }

    public function destroy(AppointmentTypes $appointmentType)
    {
        if ($appointmentType->doctor_appointment_types()->exists()) {
            return back()->withErrors([
                'appointment_type' => 'Cannot delete this type because it is assigned to a doctor.'
            ]);
        }

        $appointmentType->delete();

        return back()->with(
            'success',
            'Appointment type deleted successfully.'
        );
    }
}
