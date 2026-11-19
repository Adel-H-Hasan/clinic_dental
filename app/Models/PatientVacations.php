<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\MedicalNotes;

class PatientVacations extends Model
{
    public function patient(){
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function medical_note(){
        return $this->belongsTo(MedicalNotes::class);
    }

}
