<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        if ($this->doctor_id && $this->user_id) {
            return Auth::user()->account_type == 'doctor' ? $this->user?->name : $this->doctor?->name;
        } elseif ($this->doctor_id && $this->hospital_id) {
            return Auth::user()->account_type == 'doctor' ? $this->hospital?->user?->name : $this->doctor?->name;
        } elseif ($this->hospital_id && $this->user_id) {
            return Auth::user()->account_type == 'hospital' ? $this->user?->name : $this->hospital?->user?->name;
        }

        return 'Unknown';
    }

    public function getEmailAttribute()
    {
        if ($this->doctor_id && $this->user_id) {
            return Auth::user()->account_type == 'doctor' ? $this->user?->email : $this->doctor?->email;
        } elseif ($this->doctor_id && $this->hospital_id) {
            return Auth::user()->account_type == 'doctor' ? $this->hospital?->user?->email : $this->doctor?->email;
        } elseif ($this->hospital_id && $this->user_id) {
            return Auth::user()->account_type == 'hospital' ? $this->user?->email : $this->hospital?->user?->email;
        }

        return 'Unknown';
    }

    public function getCountryAttribute()
    {
        return $this->user?->country?->{'name_' . app()->getLocale()};
    }

    public function getAccountTypeAttribute()
    {
        return __('dashboard.' . $this->attributes['account_type']);
    }

    // Relationships for relation managers
    public function healthTips()
    {
        return $this->hasMany(HealthTip::class, 'user_id', 'doctor_id');
    }

    public function patientMedications()
    {
        return $this->hasMany(PatientMedications::class, 'user_id', 'user_id');
    }

    public function chemotherapySessions()
    {
        return $this->hasMany(ChemotherapySession::class, 'user_id', 'user_id');
    }

    public function patientAppointments()
    {
        return $this->hasMany(PatientAppointments::class, 'user_id', 'user_id');
    }

    public function patientFoods()
    {
        return $this->hasMany(PatientFood::class, 'user_id', 'user_id');
    }

    public function patientHealthReports()
    {
        return $this->hasMany(PatientHealthReport::class, 'user_id', 'user_id');
    }

    public function patientNotes()
    {
        return $this->hasMany(PatientNote::class, 'user_id', 'user_id');
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
