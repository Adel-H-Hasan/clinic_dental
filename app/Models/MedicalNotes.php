<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorProfile;
use App\Models\Appointments;

class MedicalNotes extends Model
{
    //

    public function doctor_profile(){
        return $this->belongsTo(DoctorProfile::class);
    }

    public function appointments(){
        return $this->belongsTo(Appointments::class);
    }
}
