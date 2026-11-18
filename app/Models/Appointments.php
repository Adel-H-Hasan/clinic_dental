<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DoctorAppointmentTypes;
use App\Models\DoctorReviews;
use App\Models\MedicalNotes;

class Appointments extends Model
{
    //

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function doctor_appointment_type(){
        return $this->belongsTo(DoctorAppointmentTypes::class);
    }

    public function doctor_review(){
        return $this->hasOne(DoctorReviews::class);
    }

    public function medical_notes(){
        return $this->hasMany(MedicalNotes::class);
    }


}
