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


    public static function getNavigationLabel(): string
    {
        return __('dashboard.chats');
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
                ->label(__('dashboard.patient'))
                ->required()
                ->options(User::where('account_type', 'patient')->pluck('name', 'id'))
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
            ->query(ChatRoom::query()->where('doctor_id', Auth::id())->orderBy('id', 'DESC'))
            ->columns([
                TextColumn::make('patient.name')->label('Patient')->searchable(),
                TextColumn::make('created_at')->date('Y-m-d')->label('Created')->sortable(),
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
