<?php

namespace App\Filament\Pages\Auth;

use App\Models\Hospital;
use Illuminate\Support\Facades\Cookie;
use Filament\Pages\Auth\Login as BaseLogin;

class CustomLogin extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.custom-login';

    // public function mount(): void
    // {
    //     $hospitalId = request()->get('hospital') ?? Cookie::get('current_hospital_id');
    //     $hospital = Hospital::find($hospitalId);

    //     if ($hospital) {
    //         // dd('asdf');
    //         // هنا ممكن تعمل redirect لصفحة login العادية
    //         // return redirect()->route('filament.admin.auth.login');
    //     }
    // }
}
