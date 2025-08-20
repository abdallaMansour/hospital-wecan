<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalUserAttachment extends Model
{
    use HasFactory;

    protected $table = 'hospital_user_attachments';

    protected $fillable = [
        'hospital_id',
        'user_id',
        'doctor_id',
        'status',
        'account_type',
        'sender_id',
    ];

    public function getDisplayNameAttribute()
    {
        if ($this->user_id) {
            return $this->user?->name;
        } elseif ($this->doctor_id) {
            return $this->doctor?->name;
        } elseif ($this->hospital_id) {
            return $this->hospital?->user?->name;
        }

        return 'Unknown';
    }

    public function getEmailAttribute()
    {
        if ($this->user_id) {
            return $this->user?->email;
        } elseif ($this->doctor_id) {
            return $this->doctor?->email;
        } elseif ($this->hospital_id) {
            return $this->hospital?->user?->email;
        }

        return 'Unknown';
    }

    public function getCountryAttribute()
    {
        return $this->user?->country?->{'name_' . app()->getLocale()};
    }

    public function getAccountTypeAttribute()
    {
        return __('dashboard.' . $this->user?->account_type);
    }



    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
