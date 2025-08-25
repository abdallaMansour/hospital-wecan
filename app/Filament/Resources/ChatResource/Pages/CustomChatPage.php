<?php

namespace App\Filament\Resources\ChatResource\Pages;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Hospital;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CustomChatPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.pages.custom-chat';

    protected static ?string $slug = 'custom-chat';

    // Hide from navigation - only accessible via chat actions
    protected static bool $shouldRegisterNavigation = false;

    public ?ChatRoom $chatRoom = null;
    public $otherUser = null;
    public string $message = '';
    public $attachment = null;

    // can you hide it from navigation

    public function mount(Request $request): void
    {
        $otherUserId = $request->get('other_user_id');
        $otherDoctorId = $request->get('other_doctor_id');
        $otherHospitalId = $request->get('other_hospital_id');

        if ($otherUserId) {
            $this->otherUser = User::find($otherUserId);
        } else if ($otherDoctorId) {
            $this->otherUser = User::find($otherDoctorId);
        } else if ($otherHospitalId) {
            $this->otherUser = Hospital::find($otherHospitalId)?->user;
        }

        if ($this->otherUser) {
            $this->findOrCreateChatRoom($this->otherUser);
            // Mark messages as read when opening the chat
            $this->markMessagesAsRead();
        }
    }

    public function updated($propertyName): void
    {
        // Mark messages as read when the page is updated (user is active)
        if ($this->chatRoom) {
            $this->markMessagesAsRead();
        }
    }

    public function updatedMessage(): void
    {
        // Mark messages as read when user starts typing
        if ($this->chatRoom) {
            $this->markMessagesAsRead();
        }
    }

    protected function findOrCreateChatRoom($otherUserRecord): void
    {
        if (Auth::user()->account_type === 'user') {
            $currentUser = Auth::user()->parent;
        } else {
            $currentUser = Auth::user();
        }


        // Determine who is the doctor and who is the patient
        $doctorId = null;
        $patientId = null;
        $hospitalId = null;

        if ($currentUser->account_type === 'doctor' && $otherUserRecord->account_type === 'patient') {
            $doctorId = $currentUser->id;
            $patientId = $otherUserRecord->id;
        } else if ($currentUser->account_type === 'doctor' && $otherUserRecord->account_type === 'hospital') {
            $doctorId = $currentUser->id;
            $hospitalId = $otherUserRecord->hospital_id;
        } else if ($currentUser->account_type === 'hospital' && $otherUserRecord->account_type === 'doctor') {
            $hospitalId = $currentUser->hospital_id;
            $doctorId = $otherUserRecord->id;
        } else if ($currentUser->account_type === 'hospital' && $otherUserRecord->account_type === 'patient') {
            $hospitalId = $currentUser->hospital_id;
            $patientId = $otherUserRecord->id;
        } else if ($currentUser->account_type === 'user' && $otherUserRecord->account_type === 'doctor') {
            $parent = $currentUser->parent;

            if ($parent->account_type === 'doctor' && $otherUserRecord->account_type === 'patient') {
                $doctorId = $parent->id;
                $patientId = $otherUserRecord->id;
            } else if ($parent->account_type === 'doctor' && $otherUserRecord->account_type === 'hospital') {
                $doctorId = $parent->id;
                $hospitalId = $otherUserRecord->hospital_id;
            } else if ($parent->account_type === 'hospital' && $otherUserRecord->account_type === 'doctor') {
                $hospitalId = $parent->hospital_id;
                $doctorId = $otherUserRecord->id;
            } else if ($parent->account_type === 'hospital' && $otherUserRecord->account_type === 'patient') {
                $hospitalId = $parent->hospital_id;
                $patientId = $otherUserRecord->id;
            }
        }

        // Check if chat room already exists
        if ($doctorId && $patientId) {
            $this->chatRoom = ChatRoom::where('doctor_id', $doctorId)
                ->where('patient_id', $patientId)
                ->first();
        } else if ($doctorId && $hospitalId) {
            $this->chatRoom = ChatRoom::where('doctor_id', $doctorId)
                ->where('hospital_id', $hospitalId)
                ->first();
        } else if ($patientId && $hospitalId) {
            $this->chatRoom = ChatRoom::where('patient_id', $patientId)
                ->where('hospital_id', $hospitalId)
                ->first();
        }

        if (!$this->chatRoom) {
            // Create new chat room
            $this->chatRoom = ChatRoom::create([
                'patient_id' => $patientId ?? null,
                'hospital_id' => $hospitalId ?? null,
                'doctor_id' => $doctorId ?? null,
                'name' => 'Doctor-Patient Chat',
            ]);
        }
    }

    public function sendMessage(): void
    {
        if (Auth::user()->account_type === 'user') {
            $currentUser = Auth::user()->parent;
        } else {
            $currentUser = Auth::user();
        }

        if ((empty($this->message) && !$this->attachment) || !$this->chatRoom) {
            return;
        }

        $attachmentPath = null;

        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('chat-attachments', 'public');
        }

        ChatMessage::create([
            'chat_room_id' => $this->chatRoom->id,
            'user_id' => $currentUser->id,
            'message' => $this->message,
            'attachment_path' => $attachmentPath,
        ]);

        $this->message = '';
        $this->attachment = null;
        $this->dispatch('message-sent');

        // Mark messages as read when user sends a message
        $this->markMessagesAsRead();
    }

    public function markMessagesAsRead(): void
    {
        if (!$this->chatRoom) {
            return;
        }

        // Mark all messages from the other user as read
        ChatMessage::where('chat_room_id', $this->chatRoom->id)
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function getUnreadCount(): int
    {
        if (!$this->chatRoom) {
            return 0;
        }

        return ChatMessage::where('chat_room_id', $this->chatRoom->id)
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->count();
    }

    public static function getTotalUnreadCount(): int
    {
        $currentUser = Auth::user();

        return ChatMessage::whereHas('chatRoom', function ($query) use ($currentUser) {
            $query->where('doctor_id', $currentUser->id)
                ->orWhere('patient_id', $currentUser->id);
        })
            ->where('user_id', '!=', $currentUser->id)
            ->where('is_read', false)
            ->count();
    }

    public function getMessages()
    {
        if (!$this->chatRoom) {
            return collect();
        }

        return ChatMessage::where('chat_room_id', $this->chatRoom->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function refreshMessages()
    {
        // Mark messages as read when refreshing
        $this->markMessagesAsRead();

        // This method will be called by Livewire to refresh messages
        $this->dispatch('messages-refreshed');
    }

    public function getTitle(): string
    {
        if ($this->otherUser) {
            return 'Chat with ' . $this->otherUser->name;
        }

        return 'Chat';
    }

    public function formatFileSize($size): string
    {
        if ($size === null) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    public function translateMessage(Request $request)
    {
        try {
            $messageId = $request->input('message_id');
            $message = ChatMessage::find($messageId);
            
            if (!$message) {
                return response()->json(['error' => 'Message not found'], 404);
            }

            $tr = new GoogleTranslate();
            
            // Simple language detection based on character patterns
            $text = $message->message;
            $arabicPattern = '/[\x{0600}-\x{06FF}]/u'; // Arabic Unicode range
            $isArabic = preg_match($arabicPattern, $text);
            
            // Set target language based on detected source
            if ($isArabic) {
                $tr->setSource('ar');
                $tr->setTarget('en');
                $targetLanguage = 'English';
                $sourceLanguage = 'Arabic';
            } else {
                $tr->setSource('en');
                $tr->setTarget('ar');
                $targetLanguage = 'العربية';
                $sourceLanguage = 'English';
            }
            
            $translatedText = $tr->translate($text);
            
            return response()->json([
                'success' => true,
                'translated_text' => $translatedText,
                'original_text' => $message->message,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Translation failed: ' . $e->getMessage()], 500);
        }
    }
}
