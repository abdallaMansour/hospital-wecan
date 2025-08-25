<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PatientAppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'patientAppointments';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Check if the record has a user relationship and the user is a patient
        return $ownerRecord->user && $ownerRecord->user->account_type === 'patient';
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('dashboard.patient_appointments');
    }
    public static function getModelLabel(): string
    {
        return __('dashboard.patient_appointments');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.patient_appointments');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('doctor_name')
                    ->required()
                    ->label(__('dashboard.doctor'))
                    ->maxLength(255),
                DateTimePicker::make('datetime')
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
                Hidden::make('show')->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('show', true))
            ->recordTitleAttribute('doctor_name')
            ->columns([
                Tables\Columns\TextColumn::make('doctor_name')
                    ->label(__('dashboard.doctor')),
                Tables\Columns\TextColumn::make('datetime')
                    ->label(__('dashboard.datetime')),
                Tables\Columns\TextColumn::make('instructions')
                    ->label(__('dashboard.instructions')),
                Tables\Columns\TextColumn::make('notes')
                    ->label(__('dashboard.notes')),
                Tables\Columns\TextColumn::make('logUser.name')
                    ->label(__('dashboard.log_user')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set the user_id to the user_id from the parent record
                        $ownerRecord = $this->getOwnerRecord();
                        $userId = $ownerRecord->user_id;
                        
                        // Validate that user_id is not null
                        if (!$userId) {
                            throw new \Exception('User ID cannot be null. Please ensure the attachment has a valid user.');
                        }
                        
                        $data['user_id'] = $userId;
                        return $data;
                    }),
            ])
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
