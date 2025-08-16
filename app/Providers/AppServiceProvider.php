<?php

namespace App\Providers;

use App\Models\Hospital;
use Filament\Facades\Filament;
use Filament\Events\ServingFilament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Filament\Pages\Auth\CustomLogin;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::serving(function (ServingFilament $event) {
            $hospital_key = request()->get('hospital') ?? Cookie::get('current_hospital_id');

            $hospital = Hospital::where('key', $hospital_key)->first();

            if (!$hospital) {
                $hospital = Auth::user()?->account_type === 'hospital' ? Auth::user()?->hospital : null;
            }

            if ($hospital) {
                // set the hospital id into cookie for 30 days
                Cookie::queue('current_hospital_id', $hospital->key, 60 * 24 * 30);

                Filament::getCurrentPanel()->brandLogo(env('ADMIN_DASHBOARD_URL') . '/storage/' . $hospital->hospital_logo)
                    ->darkModeBrandLogo(env('ADMIN_DASHBOARD_URL') . '/storage/' . $hospital->hospital_logo)
                    ->brandLogoHeight('3rem')
                    ->favicon(env('ADMIN_DASHBOARD_URL') . '/storage/' . $hospital->hospital_logo);
            }
        });

        Model::unguard();

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar',  'en'])->circular();
        });
    }
}
