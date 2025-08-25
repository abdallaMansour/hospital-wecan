<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Loggable;

class PatientFood extends Model
{
    use HasFactory, Loggable;

    protected $fillable = [
        'food_name',
        'instructions',
        'notes',
        'attachments',
        'user_id',
        'is_hospital',
        'hospital_id',
        'show',
        'log_user_id',
    ];

    protected $casts = [
        'attachments' => 'array'
    ];

    protected $appends = ['attachments_paths'];

    public function getAttachmentsPathsAttribute($value)
    {
        $imagePaths = $this->attachments;

        if (!is_array($imagePaths)) {
            return [];
        }
        $fullPaths = array_map(function ($path) {
            return url('/storage/' . $path);
        }, $imagePaths);

        return $fullPaths;
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'log_user_id');
    }
}
