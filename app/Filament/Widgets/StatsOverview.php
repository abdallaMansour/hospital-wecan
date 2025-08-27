<?php

namespace App\Filament\Widgets;

use App\Models\HospitalUserAttachment;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

/**
 * StatsOverview Widget
 * 
 * Displays statistics about users, patients, doctors, and hospitals.
 * Includes real-time unread messages count with automatic refresh every 30 seconds.
 */
class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    
    protected int | string | array $columnSpan = 'full';

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

        // Get unread messages count
        $unreadMessagesCount = $this->getUnreadMessagesCount($user);

        if ($user->account_type == 'hospital') {
            $doctorsCount = $hospital_user_attachments->whereNotNull('doctor_id')->count();
            return [
                Stat::make(__('dashboard.doctors_count'), $doctorsCount)
                    ->color('success'),
                Stat::make(__('dashboard.patient_count'), $patientsCount)
                    ->color('success'),
                Stat::make(__('dashboard.user_count'), $user_count)
                    ->color('success'),
                Stat::make(__('dashboard.unread_messages'), $unreadMessagesCount)
                    ->color($this->getUnreadMessagesColor($unreadMessagesCount))
                    ->description(__('dashboard.new_messages_received'))
                    ->url($unreadMessagesCount > 0 ? '/doctors' : null)
                    ->icon('heroicon-o-chat-bubble-left-right'),
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
                Stat::make(__('dashboard.unread_messages'), $unreadMessagesCount)
                    ->color($this->getUnreadMessagesColor($unreadMessagesCount))
                    ->description(__('dashboard.new_messages_received'))
                    ->url($unreadMessagesCount > 0 ? '/doctors' : null)
                    ->icon('heroicon-o-chat-bubble-left-right'),
            ];
        }
    }

    private function getUnreadMessagesCount($user): int
    {
        $hospital_user_attachments = HospitalUserAttachment::where('status', 'approved')->where(function ($q) use ($user) {
            if ($user->account_type == 'doctor') {
                $q->where('doctor_id', $user->id);
            } elseif ($user->account_type == 'hospital') {
                $q->where('hospital_id', $user->id);
            }
        })->get(['id', 'hospital_id', 'doctor_id', 'user_id']);

        // Get chat rooms where the user is involved
        $chatRoomIds = ChatRoom::where(function ($query) use ($user, $hospital_user_attachments) {
            if ($user->account_type === 'doctor') {

                $query->where('doctor_id', $user->id)->where(function ($q) use ($hospital_user_attachments) {
                    // Filter out null values to ensure only valid IDs are used
                    $hospitalIds = $hospital_user_attachments->pluck('hospital_id')->filter()->toArray();
                    $userIds = $hospital_user_attachments->pluck('user_id')->filter()->toArray();

                    if (!empty($hospitalIds)) {
                        $q->whereIn('hospital_id', $hospitalIds);
                    }
                    if (!empty($userIds)) {
                        $q->orWhereIn('patient_id', $userIds);
                    }
                });
            } elseif ($user->account_type === 'hospital') {
                $query->where('hospital_id', $user->id)->where(function ($q) use ($hospital_user_attachments) {
                    // Filter out null values to ensure only valid IDs are used
                    $userIds = $hospital_user_attachments->pluck('user_id')->filter()->toArray();
                    $doctorIds = $hospital_user_attachments->pluck('doctor_id')->filter()->toArray();

                    if (!empty($userIds)) {
                        $q->whereIn('patient_id', $userIds);
                    }
                    if (!empty($doctorIds)) {
                        $q->orWhereIn('doctor_id', $doctorIds);
                    }
                });
            }
        })
            ->pluck('id');

        return ChatMessage::whereIn('chat_room_id', $chatRoomIds)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
    }

    private function getUnreadMessagesColor(int $count): string
    {
        if ($count === 0) {
            return 'success';
        } elseif ($count <= 5) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
