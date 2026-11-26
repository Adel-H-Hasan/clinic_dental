<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointments;



class DoctorDashboardController extends Controller
{
    public function index(Request $request)
    {
        
    
        $doctor=$request->user()->doctor_profile;


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
        

  
          //upcoming appontments
        $upcomingAppointments = $appointments->filter(function ($appointment) {
         return $appointment->scheduled_at->isFuture()
        && $appointment->status === 'confirmed';
         });

         $nextAppointment = $upcomingAppointments
         ->sortBy('scheduled_at')
         ->first();

        $reportDemoAppointment = null;
        if (app()->environment('local') && config('services.gemini.demo_review')
            && $request->user()->email === 'yazan@taqweem.test') {
            $reportDemoAppointment = $appointments->first(
                fn (Appointments $appointment) => $appointment->status === 'completed'
                    && $appointment->reason === Appointments::AI_REPORT_DEMO_REASON
                    && $appointment->user?->email === 'ahmad@taqweem.test'
            );
        }

        return view('doctor.dashboard', compact(
           'doctor',
           'appointments',
           'todayAppointments',
           'totalAppointments',
           'pendingAppointments',
           'totalPatients',
           'upcomingAppointments',
           'appointmentsPerMonth',
           'nextAppointment',
           'reportDemoAppointment'
            ));

     

    }
}
