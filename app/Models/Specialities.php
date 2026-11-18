<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorProfile;

class Specialities extends Model
{
    //

    public function doctor_profile(){
        return $this->hasMany(DoctorProfile::class);
    }
}
