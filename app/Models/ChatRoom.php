<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'doctor_id', 'patient_id', 'hospital_id'];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}