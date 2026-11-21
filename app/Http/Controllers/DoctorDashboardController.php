<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DoctorProfile;



class DoctorDashboardController extends Controller
{
    public function index(Request $request)
    {
        
    
        $doctor = DoctorProfile::findOrFail(1);

       $appointments = $doctor->doctor_appointment_types()
         ->with([
        'appointment_type',
        'appointments.user',
        ])
           ->get()
           ->flatMap(function ($type) {
        return $type->appointments;
         });

         $todayAppointments = $appointments->filter(function ($appointment) {
        return $appointment->scheduled_at->isToday();
        })->count();

        
          $totalAppointments = $appointments->count();
          $pendingAppointments = $appointments->where('status', 'pending')->count();
         
          $totalPatients = $appointments->pluck('patient_id')->unique()->count();

          $appointmentsPerMonth = $appointments
          ->groupBy(function ($appointment) {
        return $appointment->scheduled_at->format('Y-m');
          })
         ->map(function ($monthlyAppointments) {
        return $monthlyAppointments->count();
          });
        dd($appointmentsPerMonth);

          //upcoming appontments
        $upcomingAppointments = $appointments->filter(function ($appointment) {
         return $appointment->scheduled_at->isFuture()
        && $appointment->status === 'confirmed';
         });

        return view('doctor.dashboard', compact(
           'doctor',
           'appointments',
           'todayAppointments',
           'totalAppointments',
           'pendingAppointments',
           'totalPatients',
           'upcomingAppointments'
            ));

     

    }
}