<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalReport extends Model
{
    protected $fillable = ['medical_note_id', 'content'];

    public function medical_note()
    {
        return $this->belongsTo(MedicalNotes::class, 'medical_note_id');
    }
}
