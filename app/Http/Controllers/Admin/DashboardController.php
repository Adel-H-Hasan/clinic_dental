<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Specialities;
use App\Models\Appointments;
use App\Models\User;


class DashboardController extends Controller
{
    public function index(){
        $specialitiesCount=Specialities::count();
        $appointmentsCount=Appointments::count();
        $doctorsCount=User::where('role','doctor')->count();
        $patientsCount=User::where('role','patient')->count();
        $recentAppointments=Appointments::with('user')->latest()->take(5)->get();
        $recentUsers=User::latest()->take(5)->get();
        $recentPatients=User::where('role','patient')->latest()->take(5)->get();
       

        $appointmentsByStatus=Appointments::select('status')
        ->selectRaw('COUNT(*) as total')
        ->groupBy('status')
        ->get();

        $upcomingAppointments = Appointments::where('scheduled_at', '>', now())
          ->whereIn('status', ['pending', 'confirmed'])
          ->orderBy('scheduled_at', 'asc')
          ->take(5)
          ->get();

        return view('admin.dashboard',compact
        ('specialitiesCount',
          'appointmentsCount',
          'doctorsCount',
          'patientsCount',
          'recentAppointments',
          'appointmentsByStatus',
          'recentUsers',
          'recentPatients',
          'upcomingAppointments'

        ));

        

    }
}
