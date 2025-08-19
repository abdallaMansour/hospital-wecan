<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Favoritable;
class PatientMedications extends Model
{
    use HasFactory, Favoritable;

    protected $appends = ['drug_image_path'];
    protected $fillable = [
        'drug_name',
        'frequency',
        'frequency_per',
        'instructions',
        'duration',
        'month-or-day',
        'show',
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
}
