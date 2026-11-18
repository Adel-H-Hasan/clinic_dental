<?php

namespace App\Models;

use App\Http\Controllers\DoctorAppointmentTypesController;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Specialities;
use App\Models\DoctorAppointmentTypes;
use App\Models\DoctorTimeOff;
use App\Models\DoctorReviews;
use App\Models\DoctorSchedules;
use App\Models\MedicalNotes;


class DoctorProfile extends Model
{
    //

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function speciality(){
        return $this->belongsTo(Specialities::class);
    }

    public function doctor_appointment_types(){
         return $this->hasMany(DoctorAppointmentTypes::class);
    }

     public function doctor_time_off(){
         return $this->hasMany(DoctorTimeOff::class);
    }

     public function doctor_reviews(){
         return $this->hasMany(DoctorReviews::class);
    }

     public function doctor_schedules(){
         return $this->hasMany(DoctorSchedules::class);
    }
     public function medical_notes(){
         return $this->hasMany(MedicalNotes::class);
    }
}
