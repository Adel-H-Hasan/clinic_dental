<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorAppointmentTypes;

class AppointmentTypes extends Model
{
    //

    public function doctor_appointment_types()
    {
        return $this->hasMany(DoctorAppointmentTypes::class, 'appointment_type_id');
    }

    protected $fillable = [
        'name',
        'description',
    ];
}
