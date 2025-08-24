<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Favoritable;
use Stichoza\GoogleTranslate\GoogleTranslate;

class HealthTip extends Model
{
    use HasFactory, Favoritable;

    protected $casts = [
        'attachments' => 'array',
        'publish_datetime' => 'datetime',
        'visible' => 'boolean',
    ];

    protected $appends = ['doctor_name', 'doctor_image'];

    protected $hidden = [
        'created_at', 
        'updated_at', 
        'user_id',
        'user',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDoctorNameAttribute()
    {
        return $this->user->name ?? null;
    }

    public function getDoctorImageAttribute()
    {
        return $this->user->profile_picture_path ?? null;
    }

    public function getAttachmentsAttribute($value)
    {
        return json_decode($value) ?? [];
    }

    public function toArray()
    {
        $array = parent::toArray();
        
        // Format publish_datetime
        $array['publish_datetime'] = $this->publish_datetime->format('Y-m-d H:i:s');
        
        // Ensure visible is 1 or 0
       // $array['visible'] = $this->visible ? 1 : 0;
        
        // Remove is_favorited if it exists
        unset($array['is_favorited']);
        
        // Ensure attachments is always an array
        $array['attachments'] = $this->attachments;
        
        return $array;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
        if (!empty($model->title_ar)) {
            $model->title_en = GoogleTranslate::trans($model->title_ar, 'en', 'ar');
        } elseif (!empty($model->title_en)) {
            $model->title_ar = GoogleTranslate::trans($model->title_en, 'ar', 'en');
        }

        if (!empty($model->details_ar)) {
            $model->details_en = GoogleTranslate::trans($model->details_ar, 'en', 'ar');
        } elseif (!empty($model->details_en)) {
            $model->details_ar = GoogleTranslate::trans($model->details_en, 'ar', 'en');
        }
    });
}

}