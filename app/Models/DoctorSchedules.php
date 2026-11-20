<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorProfile;

class DoctorSchedules extends Model
{
    //

    public function doctor_profile(){
        return $this->belongsTo(DoctorProfile::class);
    }
}
