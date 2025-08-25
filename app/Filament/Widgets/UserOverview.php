<?php

namespace App\Filament\Widgets;

use App\Models\HospitalUserAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;


class UserOverview extends BaseWidget
{

    public static function getHospitalId()
    {
        return Auth::user()->hospital_id;
    }

    public function authAccount()
    {
        $user = Auth::user();
        if ($user->account_type === 'user') {
            return $user->parent ?? $user;
        }
        return $user;
    }

    protected function getStats(): array
    {
        // $users = User::all();
        $user = $this->authAccount();

        if ($user->account_type == 'hospital') {
            $hospital_user_attachments = HospitalUserAttachment::where('status', 'approved')->where('hospital_id', $user->hospital_id)->get();
        } else if ($user->account_type == 'doctor') {
            $hospital_user_attachments = HospitalUserAttachment::where('status', 'approved')->where('doctor_id', $user->id)->get();
        } else {
            $hospital_user_attachments = HospitalUserAttachment::where('status', 'approved')->where('doctor_id', $user->parent_id)->get();
        }

        $user_count = $user->users()->count();

        // Filter users to get counts for patients with preferred language set to Arabic
        $patientsCount = $hospital_user_attachments->whereNotNull('user_id')->count();


        if ($user->account_type == 'hospital') {
            $doctorsCount = $hospital_user_attachments->whereNotNull('doctor_id')->count();
            return [
                Stat::make(__('dashboard.doctors_count'), $doctorsCount)
                    ->color('success'),
                Stat::make(__('dashboard.patient_count'), $patientsCount)
                    ->color('success'),
                Stat::make(__('dashboard.user_count'), $user_count)
                    ->color('success'),
            ];
        } else {
            // Filter users to get counts for doctors with profession set in Arabic
            $hospitalsCount = $hospital_user_attachments->whereNotNull('hospital_id')->count();
            return [
                Stat::make(__('dashboard.patient_count'), $patientsCount)
                    ->color('success'),
                Stat::make(__('dashboard.hospitals_count'), $hospitalsCount)
                    ->color('success'),
                Stat::make(__('dashboard.user_count'), $user_count)
                    ->color('success'),
            ];
        }
    }
}
