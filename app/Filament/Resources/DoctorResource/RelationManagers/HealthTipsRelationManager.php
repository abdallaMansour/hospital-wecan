<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class HealthTipsRelationManager extends RelationManager
{
    protected static string $relationship = 'healthTips';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('dashboard.health_tip');
    }
    public static function getModelLabel(): string
    {
        return __('dashboard.health_tip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.health_tip');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Check if the record has a doctor relationship
        return $ownerRecord->doctor && $ownerRecord->doctor->account_type === 'doctor';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_' . app()->getLocale())
                    ->label(__('dashboard.title_' . app()->getLocale()))
                    ->maxLength(255),
                Textarea::make('details_' . app()->getLocale())
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.details_' . app()->getLocale())),
                DateTimePicker::make('publish_datetime')
                    ->required()
                    ->label(__('dashboard.publish_datetime')),
                FileUpload::make('attachments')
                    ->label(__('dashboard.attachments'))
                    ->visibility('public')
                    ->multiple(),
                Select::make('tip_type')
                    ->label(__('dashboard.tip_type'))
                    ->options([
                        'Medication Tips' => __('dashboard.medication_tips'),
                        'General Tips' => __('dashboard.general_tips'),
                        'Nutrition Tips' => __('dashboard.nutrition_tips'),
                        'Dosage Tips' => __('dashboard.dosage_tips'),
                        'Other' => __('dashboard.other'),
                    ])
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('link')
                    ->label(__('dashboard.link')),
                Hidden::make('visible')->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('visible', true))
            ->recordTitleAttribute('title_' . app()->getLocale())
            ->columns([
                Tables\Columns\TextColumn::make('title_' . app()->getLocale())->label(__('dashboard.title_' . app()->getLocale())),
                Tables\Columns\TextColumn::make('tip_type')->label(__('dashboard.tip_type'))
                    ->getStateUsing(function ($record) {
                        return __('dashboard.' . $record->tip_type);
                    }),
                Tables\Columns\TextColumn::make('link')->label(__('dashboard.link')),
                Tables\Columns\TextColumn::make('publish_datetime')->label(__('dashboard.publish_datetime')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set the user_id to the doctor_id from the parent record
                        $data['user_id'] = $this->getOwnerRecord()->doctor_id;
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
