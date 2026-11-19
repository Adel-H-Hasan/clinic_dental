<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppointmentTypes;
use App\Models\DoctorProfile;
use App\Models\Appointments;
class DoctorAppointmentTypes extends Model
{
    //

    public function appointment_type(){
        return $this->belongsTo(AppointmentTypes::class);
    }

     public function doctor_profile(){
        return $this->belongsTo(DoctorProfile::class);
    }

     public function appointments(){
        return $this->hasMany(Appointments::class, 'doctor_appointment_type_id');
    }
}
