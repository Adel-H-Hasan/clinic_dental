<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AiImageAnalyses extends Model
{
    protected $fillable = [
        'user_id',
        'image_url',
        'before_image',
        'after_image',
        'ai_analyses',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
