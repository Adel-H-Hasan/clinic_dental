<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointments;


class DoctorDashboardController extends Controller
{
    public function index(){
        $doctorProfile = auth()->user()->doctor_profile;

        return view('doctor.dashboard');
    }
}
