<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'appointment_id',
        'amount',
        'currency',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'paid_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointments::class);
    }
}