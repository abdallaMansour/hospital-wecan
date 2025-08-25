<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Loggable;

class ChemotherapySession extends Model
{
    use HasFactory, Loggable;

    protected $fillable = [
        'session_number',
        'session_datetime',
        'instructions',
        'notes',
        'user_id',
        'show',
        'log_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'log_user_id');
    }
}
