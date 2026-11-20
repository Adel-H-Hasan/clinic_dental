<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorProfile;

class Specialities extends Model
{
    //
    protected $fillable=[
        'name'
    ];

    public function doctor_profile(){
        return $this->hasMany(DoctorProfile::class, 'speciality_id');
    }
}
