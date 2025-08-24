<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Resources\ChatResource\Pages\CustomChatPage;

Route::post('/translate-message', [CustomChatPage::class, 'translateMessage'])->middleware('auth');


