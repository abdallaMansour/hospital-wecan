<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Favoritable;
class Cancer extends Model
{
   use HasFactory,Favoritable;

    protected $appends = ['cancer_image_path'];

    public function getCancerImagePathAttribute($value)
    {
        return $this->attributes['cancer_image'] ? (file_exists(storage_path('app/public/' . $this->attributes['cancer_image'])) ? $this->attributes['cancer_image'] : env('ADMIN_DASHBOARD_URL') . '/storage/' . $this->attributes['cancer_image']) : '';
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }


    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
