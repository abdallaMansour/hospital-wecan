<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_name',
        'hospital_logo',
        'user_name',
        'email',
        'contact_number',
        'country_id',
        'city',
        'key'
    ];

    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            $model->key = (string) Str::uuid();
        });
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function attachedDoctors()
    {
        return $this->belongsToMany(User::class, 'hospital_user_attachments', 'hospital_id', 'user_id')
                    ->where('account_type', 'doctor')
                    ->withPivot('status', 'sender_id');
    }

    public function attachedPatients()
    {
        return $this->belongsToMany(User::class, 'hospital_user_attachments', 'hospital_id', 'user_id')
                    ->where('account_type', 'patient')
                    ->withPivot('status', 'sender_id');
    }
    public function user()
{
    return $this->belongsTo(User::class);
}
}