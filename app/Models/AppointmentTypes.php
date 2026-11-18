<?php

namespace App\Models;

use App\Http\Controllers\DoctorAppointmentTypesController;
use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorAppointmentTypes;

class AppointmentTypes extends Model
{
    //

    public function doctor_appointment_types(){
        return $this->hasMany(DoctorAppointmentTypes::class);
    }
}
