<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PatientMedicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'patientMedications';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Check if the record has a user relationship and the user is a patient
        return $ownerRecord->user && $ownerRecord->user->account_type === 'patient';
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('dashboard.patient_medications');
    }
    public static function getModelLabel(): string
    {
        return __('dashboard.patient_medications');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.patient_medications');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('drug_name')
                    ->label(__('dashboard.drug_name'))
                    ->required()
                    ->maxLength(255),
                FileUpload::make('drug_image')
                    ->label(__('dashboard.drug_image'))
                    ->visibility('public')->image()
                    ->imageEditor()
                    ->maxSize(2048),
                Forms\Components\TextInput::make('frequency')
                    ->label(__('dashboard.frequency'))
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Select::make('frequency_per')
                    ->required()
                    ->label(__('dashboard.frequency_per'))
                    ->options([
                        'day' => __('dashboard.day'),
                        'week' => __('dashboard.week'),
                        'month' => __('dashboard.month'),
                    ])
                    ->searchable(),
                    Forms\Components\TextInput::make('duration')
                        ->label(__('dashboard.duration'))
                        ->required()
                        ->numeric()
                        ->maxLength(255),
                    Select::make('month-or-day')
                        ->required()
                        ->label(__('dashboard.month-or-day'))
                        ->options([
                            'day' => __('dashboard.day'),
                            'month' => __('dashboard.month'),
                        ])
                        ->searchable(),
                Textarea::make('instructions')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.instructions')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('drug_name')
            ->columns([
                ImageColumn::make('drug_image')->label(__('dashboard.drug_image')),
                Tables\Columns\TextColumn::make('drug_name')->label(__('dashboard.drug_name')),
                Tables\Columns\TextColumn::make('frequency')->label(__('dashboard.frequency')),
                Tables\Columns\TextColumn::make('frequency_per')->label(__('dashboard.frequency_per')),
                Tables\Columns\TextColumn::make('duration')->label(__('dashboard.duration')),
                Tables\Columns\TextColumn::make('month-or-day')->label(__('dashboard.month-or-day')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set the user_id to the user_id from the parent record
                        $data['user_id'] = $this->getOwnerRecord()->user_id;
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
