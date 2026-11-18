<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorProfile;
use App\Models\Appointments;
use App\Models\User;

class DoctorReviews extends Model
{
    //

    public function doctor_profile(){
        return $this->belongsTo(DoctorProfile::class);
    }

    public function appointment(){
        return $this->belongsTo(Appointments::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
