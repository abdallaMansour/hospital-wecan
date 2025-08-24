<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PatientFoodsRelationManager extends RelationManager
{
    protected static string $relationship = 'patientFoods';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Check if the record has a user relationship and the user is a patient
        return $ownerRecord->user && $ownerRecord->user->account_type === 'patient';
    }


    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('dashboard.patient_food');
    }
    public static function getModelLabel(): string
    {
        return __('dashboard.patient_food');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.patient_food');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('food_name')
                    ->required()
                    ->label(__('dashboard.food_name'))
                    ->maxLength(255),
                FileUpload::make('attachments')
                    ->label(__('dashboard.attachments'))
                    ->visibility('public')
                    ->multiple(),
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
            ->recordTitleAttribute('food_name')
            ->columns([
                Tables\Columns\TextColumn::make('food_name')
                    ->label(__('dashboard.food_name')),
                Tables\Columns\TextColumn::make('instructions')
                    ->label(__('dashboard.instructions')),
                Tables\Columns\TextColumn::make('notes')
                    ->label(__('dashboard.notes')),
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
