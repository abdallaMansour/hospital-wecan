<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class UserOverview extends BaseWidget
{

    public static function getHospitalId()
    {
        $currentUser = User::find(auth()->user()->id);
        return $currentUser->hospital_id;
    }
    protected function getStats(): array
    {
        $users = User::all();

        // Filter users to get counts for patients with preferred language set to Arabic
        $arabicPatientsCount = $users->where('account_type', 'patient')
            ->where('preferred_language', 'ar')
            ->where('hospital_id', self::getHospitalId())
            ->count();

        // Filter users to get counts for patients with preferred language set to English
        $englishPatientsCount = $users->where('account_type', 'patient')
            ->where('preferred_language', 'en')
            ->where('hospital_id', self::getHospitalId())
            ->count();

        // Filter users to get counts for doctors with profession set in Arabic
        $arabicDoctorsCount = $users->where('account_type', 'doctor')
            ->where('preferred_language', 'ar')
            ->where('hospital_id', self::getHospitalId())
            ->count();

        // Filter users to get counts for doctors with profession set in English
        $englishDoctorsCount = $users->where('account_type', 'doctor')
            ->where('preferred_language', 'en')
            ->where('hospital_id', self::getHospitalId())
            ->count();

        return [
            Stat::make(__('dashboard.arabic_patient_count'), $arabicPatientsCount),
            Stat::make(__('dashboard.english_patient_count'),  $englishPatientsCount),
            Stat::make(__('dashboard.arabic_doctors_count'), $arabicDoctorsCount),
            Stat::make(__('dashboard.english_doctors_count'), $englishDoctorsCount),
        ];
    }
}
