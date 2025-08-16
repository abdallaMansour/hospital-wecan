<?php


// app/Filament/Resources/ChatResource/Pages/CreateChat.php
namespace App\Filament\Resources\ChatResource\Pages;

use App\Models\Chat;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;
use Filament\Pages\Actions;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\ChatResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\Request;

class CreateChat extends CreateRecord
{
    protected static string $resource = ChatResource::class;
    
    public function mount(): void
    {
        parent::mount();
        
        // Pre-fill form with URL parameters
        $request = request();
        if ($request->has('patient_id')) {
            $this->form->fill([
                'patient_id' => $request->get('patient_id'),
                'hospital_id' => $request->get('hospital_id', Auth::user()->hospital_id),
            ]);
        }
    }
    
    protected function handleRecordCreation(array $data): ChatRoom
    {
        $currentUser = Auth::user();
        $otherUser = User::find($data['patient_id']);
        
        // Determine who is the doctor and who is the patient
        $doctorId = null;
        $patientId = null;
        
        if ($currentUser->account_type === 'doctor') {
            $doctorId = $currentUser->id;
            $patientId = $otherUser->id;
        } else {
            // Current user is a patient, other user is a doctor
            $doctorId = $otherUser->id;
            $patientId = $currentUser->id;
        }
        
        // Check if chat room already exists
        $existingChat = ChatRoom::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->where('hospital_id', $data['hospital_id'])
            ->first();
            
        if ($existingChat) {
            // If chat exists, redirect to view it
            $this->redirect(route('filament.admin.resources.chats.edit', ['record' => $existingChat->id]));
            return $existingChat;
        }

        $chat = ChatRoom::create([
            'patient_id' => $patientId,
            'hospital_id' => $data['hospital_id'],
            'doctor_id' => $doctorId,
            'name' => 'Doctor-Patient Chat',
        ]);

        if (isset($data['message']) && !empty($data['message'])) {
            ChatMessage::create([
                'chat_room_id' => $chat->id,
                'user_id'      => $currentUser->id,
                'message'      => $data['message'],
            ]);
        }

        return $chat;
    }
    
    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.chats.edit', ['record' => $this->record->id]);
    }
}
