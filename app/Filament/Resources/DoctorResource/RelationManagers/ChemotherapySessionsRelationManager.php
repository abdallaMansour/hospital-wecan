<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChemotherapySessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'chemotherapySessions';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->account_type === 'patient';
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('dashboard.chemotherapy_session');
    }
    public static function getModelLabel(): string
    {
        return __('dashboard.chemotherapy_session');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.chemotherapy_session');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('session_number')
                    ->label(__('dashboard.session_number'))
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('session_datetime')
                    ->required()
                    ->label(__('dashboard.datetime')),
                Textarea::make('instructions')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.instructions')),
                Textarea::make('notes')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.notes')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('session_number')
            ->columns([
                Tables\Columns\TextColumn::make('session_number')
                    ->label(__('dashboard.session_number')),
                Tables\Columns\TextColumn::make('session_datetime')
                    ->label(__('dashboard.datetime')),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
