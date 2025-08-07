<?php

// app/Filament/Resources/ChatResource.php
namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Chat;
use Filament\Tables;
use App\Models\ChatRoom;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ChatMessage;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\ChatResource\Pages;
use App\Filament\Resources\ChatResource\RelationManagers\MessagesRelationManager;

class ChatResource extends Resource
{
    protected static ?string $model = ChatRoom::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    public static function getModelLabel(): string
    {
        return __('dashboard.chats');
    }


    public static function getNavigationLabel(): string
    {
        return __('dashboard.chats');
    }

    public static function getHospitalId()
    {
        return auth()->user()->hospital_id;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('user_id')
                ->label('User ID')
                ->required(),
            Textarea::make('message')
                ->label('Message')
                ->required(),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->query(ChatRoom::query()->where('hospital_id', self::getHospitalId())->orderBy('id', 'DESC'))
            ->columns([
            TextColumn::make('patient.name')->label('Patient'),
            // TextColumn::make('doctor.name')->label('Doctor'),
            // TextColumn::make('message')->label('Message'),
            TextColumn::make('created_at')->date('Y-m-d')->label('Sent At')->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChats::route('/'),
            'create' => Pages\CreateChat::route('/create'),
            'edit' => Pages\EditChat::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
{
    return [
        MessagesRelationManager::class,
    ];
}
}
