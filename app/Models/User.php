<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Favoritable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens, Favoritable;

    protected $appends = ['profile_picture_path'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->account_type == 'admin' || $this->account_type == 'hospital' || $this->account_type == 'doctor';
    }

    public function healthTips(): HasMany
    {
        return $this->hasMany(HealthTip::class);
    }

    public function patientMedications(): HasMany
    {
        return $this->hasMany(PatientMedications::class);
    }

    public function chemotherapySessions(): HasMany
    {
        return $this->hasMany(ChemotherapySession::class);
    }

    public function patientAppointments(): HasMany
    {
        return $this->hasMany(PatientAppointments::class);
    }

    public function patientFoods(): HasMany
    {
        return $this->hasMany(PatientFood::class);
    }

    public function patientHealthReports(): HasMany
    {
        return $this->hasMany(PatientHealthReport::class);
    }

    public function patientNotes(): HasMany
    {
        return $this->hasMany(PatientNote::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getProfilePicturePathAttribute($value)
    {
        if (array_key_exists('profile_picture', $this->attributes)) {
            return $this->attributes['profile_picture'] ? url('/storage/' . $this->attributes['profile_picture']) : '';
        }
        return '';
    }

    public function doctorChatRooms(): HasMany
    {
        return $this->hasMany(ChatRoom::class, 'doctor_id');
    }

    public function patientChatRooms(): HasMany
    {
        return $this->hasMany(ChatRoom::class, 'patient_id');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    // Check if the user is associated with an active hospital
    public function hasActiveHospital(): bool
    {
        return $this->hospital && $this->hospital->account_status === 'active';
    }

    // Check if the user is a pending hospital
    public function isPendingHospital(): bool
    {
        return $this->account_type === 'hospital' && $this->account_status === 'pending';
    }
    public function hospitalChatRooms(): HasMany
    {
        return $this->hasMany(ChatRoom::class, 'hospital_id');
    }

    public function chatRooms(): HasMany
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function attachedDoctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_attachments', 'patient_id', 'doctor_id')
            ->where('account_type', 'doctor')
            ->withPivot('status', 'sender_id');
    }

    public function attachedPatients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_attachments', 'doctor_id', 'patient_id')
            ->where('account_type', 'patient')
            ->withPivot('status', 'sender_id');
    }

    public function attachedHospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, 'hospital_user_attachments', 'user_id', 'hospital_id')
            ->withPivot('status', 'sender_id');
    }

    public function hospitalAttachedDoctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'hospital_user_attachments', 'hospital_id', 'user_id')
            ->where('account_type', 'doctor')
            ->withPivot('status', 'sender_id');
    }

    public function hospitalAttachedPatients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'hospital_user_attachments', 'hospital_id', 'user_id')
            ->where('account_type', 'patient')
            ->withPivot('status', 'sender_id');
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
