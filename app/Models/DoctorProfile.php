<?php

namespace App\Models;

use App\Http\Controllers\DoctorAppointmentTypesController;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Specialities;
use App\Models\DoctorAppointmentTypes;
use App\Models\DentalReviews;
use App\Models\DoctorSchedules;
use App\Models\MedicalNotes;


class DoctorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'speciality_id',
        'license_number',
        'consultation_fee'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function speciality(){
        return $this->belongsTo(Specialities::class);
    }

    public function doctor_appointment_types(){
         return $this->hasMany(DoctorAppointmentTypes::class);
    }

    public function dental_reviews(){
         return $this->hasMany(DentalReviews::class);
    }

     public function doctor_schedules(){
         return $this->hasMany(DoctorSchedules::class);
    }
     public function medical_notes(){
         return $this->hasMany(MedicalNotes::class);
    }
}
