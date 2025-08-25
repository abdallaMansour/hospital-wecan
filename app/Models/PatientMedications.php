<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Favoritable;
use App\Traits\Loggable;

class PatientMedications extends Model
{
    use HasFactory, Favoritable, Loggable;

    protected $appends = ['drug_image_path'];
    protected $fillable = [
        'drug_name',
        'frequency',
        'frequency_per',
        'instructions',
        'duration',
        'month-or-day',
        'show',
        'user_id',
        'doctor_id',
        'log_user_id',
    ];
    protected $attributes = [
        'show' => false,
    ];
    public function getDrugImagePathAttribute($value)
    {
        return $this->drug_image ? url('/storage/' . $this->drug_image) : '';
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function logUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'log_user_id');
    }
}
