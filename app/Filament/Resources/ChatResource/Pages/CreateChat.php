<?php


// app/Filament/Resources/ChatResource/Pages/CreateChat.php
namespace App\Filament\Resources\ChatResource\Pages;

use App\Models\Chat;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Filament\Pages\Actions;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\ChatResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChat extends CreateRecord
{
    protected static string $resource = ChatResource::class;
    protected function handleRecordCreation(array $data): ChatRoom
    {
        $chat = ChatRoom::create([
            'patient_id' => $data['patient_id'],
            'hospital_id' => $data['hospital_id'],
            'doctor_id' => Auth::id(),
            'name' => 'Doctor-Patient Chat',
        ]);

        ChatMessage::create([
            'chat_room_id' => $chat->id,
            'user_id'      => Auth::id(),
            'message'      => $data['message'],
        ]);

        return $chat;
    }
    // Customize the form here (it’s inherited from the resource)
    // protected function getRedirectUrl(): string
    // {
    //     return static::getUrl('index'); // Redirect to the index page after creating the chat
    // }

    // // Optionally, you can add additional actions here, for example, for custom logic after chat creation
    // protected function getActions(): array
    // {
    //     return [
    //         Actions\SaveAction::make(), // Default save action to save the record
    //     ];
    // }
}
