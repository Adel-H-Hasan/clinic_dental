<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DoctorAppointmentTypes;
use App\Models\DentalReviews;
use App\Models\MedicalNotes;
use App\Models\Payment;

class Appointments extends Model
{
    public const AI_REPORT_DEMO_REASON = 'LOCAL-TEST: زيارة تجريبية – تقرير AI';

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    protected $fillable = [
        'patient_id',
        'doctor_appointment_type_id',
        'scheduled_at',
        'status',
        'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor_appointment_type()
    {
        return $this->belongsTo(DoctorAppointmentTypes::class);
    }

    public function dental_review()
    {
        return $this->hasOne(DentalReviews::class, 'appointment_id');
    }

    public function medical_notes()
    {
        return $this->hasMany(MedicalNotes::class, 'appointment_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'appointment_id');
    }
}