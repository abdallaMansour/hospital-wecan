<?php


// app/Filament/Resources/ChatResource/Pages/CreateChat.php
namespace App\Filament\Resources\ChatResource\Pages;

use App\Filament\Resources\ChatResource;
use App\Models\Chat;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChat extends CreateRecord
{
    protected static string $resource = ChatResource::class;

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
