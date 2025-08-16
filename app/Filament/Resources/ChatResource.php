<?php

// app/Filament/Resources/ChatResource.php
namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Chat;
use App\Models\User;
use Filament\Tables;
use App\Models\ChatRoom;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ChatMessage;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\ChatResource\Pages;
use App\Filament\Resources\ChatResource\RelationManagers\MessagesRelationManager;

class ChatResource extends Resource
{
    protected static ?string $model = ChatRoom::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.chats');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.chats');
    }

    // hide from navigation
    public static function canAccess(): bool
    {
        return false;
    }


    public static function getNavigationLabel(): string
    {
        $unreadCount = \App\Filament\Resources\ChatResource\Pages\CustomChatPage::getTotalUnreadCount();
        $label = __('dashboard.chats');
        
        if ($unreadCount > 0) {
            $label .= " ({$unreadCount})";
        }
        
        return $label;
    }

    public static function getHospitalId()
    {
        return optional(Auth::user())->hospital_id;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('patient.name')->label('Patient'),
            TextEntry::make('created_at')->date('Y-m-d')->label('Created'),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('patient_id')
                ->label(__('dashboard.chat_with'))
                ->required()
                ->options(function () {
                    $currentUser = Auth::user();
                    $query = User::where('id', '!=', $currentUser->id);
                    
                    // If current user is a doctor, show patients and other doctors
                    if ($currentUser->account_type === 'doctor') {
                        $query->where(function ($q) {
                            $q->where('account_type', 'patient')
                              ->orWhere('account_type', 'doctor');
                        });
                    } else {
                        // If current user is a patient, show doctors
                        $query->where('account_type', 'doctor');
                    }
                    
                    return $query->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->hidden(fn (string $operation): bool => $operation === 'edit'),
            TextInput::make('message')
                ->label(__('dashboard.message'))
                ->hidden(fn (string $operation): bool => $operation === 'edit')
                ->required(fn (string $operation): bool => $operation === 'create'),
            Hidden::make('hospital_id')
                ->default(self::getHospitalId()),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->query(ChatRoom::query()
                ->where(function ($query) {
                    $query->where('doctor_id', Auth::id())
                          ->orWhere('patient_id', Auth::id());
                })
                ->orderBy('id', 'DESC'))
            ->columns([
                TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->visible(fn () => Auth::user()->account_type === 'patient'),
                TextColumn::make('patient.name')
                    ->label('Patient')
                    ->searchable()
                    ->visible(fn () => Auth::user()->account_type === 'doctor'),
                TextColumn::make('other_user')
                    ->label('Chat With')
                    ->getStateUsing(function (ChatRoom $record) {
                        $currentUser = Auth::user();
                        if ($record->doctor_id === $currentUser->id) {
                            return $record->patient->name;
                        } else {
                            return $record->doctor->name;
                        }
                    })
                    ->searchable(),
                TextColumn::make('last_activity')
                    ->label('Last Activity')
                    ->getStateUsing(function (ChatRoom $record) {
                        $lastMessage = ChatMessage::where('chat_room_id', $record->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        return $lastMessage ? $lastMessage->created_at->diffForHumans() : 'No activity';
                    })
                    ->sortable(),
                TextColumn::make('last_message')
                    ->label('Last Message')
                    ->getStateUsing(function (ChatRoom $record) {
                        $lastMessage = ChatMessage::where('chat_room_id', $record->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if (!$lastMessage) {
                            return 'No messages yet';
                        }
                        
                        $currentUser = Auth::user();
                        $isFromCurrentUser = $lastMessage->user_id === $currentUser->id;
                        $readStatus = $isFromCurrentUser ? ($lastMessage->is_read ? '✓✓' : '✓') : '';
                        
                        return $lastMessage->message . ' ' . $readStatus;
                    })
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('unread_count')
                    ->label('Unread')
                    ->getStateUsing(function (ChatRoom $record) {
                        $currentUser = Auth::user();
                        return ChatMessage::where('chat_room_id', $record->id)
                            ->where('user_id', '!=', $currentUser->id)
                            ->where('is_read', false)
                            ->count();
                    })
                    ->badge()
                    ->color('danger')
                    ->visible(fn () => true),
            ])
            ->actions([
                Tables\Actions\Action::make('open_chat')
                    ->label(__('dashboard.chat'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->url(function (ChatRoom $record): string {
                        $currentUser = Auth::user();
                        $otherUserId = $record->doctor_id === $currentUser->id ? $record->patient_id : $record->doctor_id;
                        return '/custom-chat?other_user_id=' . $otherUserId . '&hospital_id=' . $record->hospital_id;
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChats::route('/'),
            'create' => Pages\CreateChat::route('/create'),
            'edit' => Pages\EditChat::route('/{record}/edit'),
            // 'view' => Pages\ViewChat::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
        ];
    }
}
