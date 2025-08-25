<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->log_user_id = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->log_user_id = Auth::id();
            }
        });
    }
}
