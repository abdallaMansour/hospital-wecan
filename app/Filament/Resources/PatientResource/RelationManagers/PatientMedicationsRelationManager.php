<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PatientMedications;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PatientMedicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'patientMedications';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->account_type === 'patient';
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

    public static function getHospitalId()
    {
        $currentUser = User::find(auth()->user()->id);
        return $currentUser->hospital_id;
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

                TextInput::make('duration')
                    ->required()
                    ->label(__('dashboard.of_duration'))
                    ->numeric(),

                Select::make('month-or-day')
                    ->required()
                    ->label(__('dashboard.day/month'))
                    ->options([
                        'day' => __('dashboard.day'),
                        'week' => __('dashboard.week'),
                        'month' => __('dashboard.month'),
                    ]),
                Textarea::make('instructions')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.instructions')),
                Hidden::make('show')
                    ->default(true),
                Hidden::make('doctor_id')
                    ->default(auth()->id()),
                Hidden::make('hospital_id')
                    ->default(self::getHospitalId()),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(PatientMedications::query()->where('show', true)->orderBy('id', 'DESC'))
            ->recordTitleAttribute('drug_name')
            ->columns([
                ImageColumn::make('drug_image')->label(__('dashboard.drug_image')),
                Tables\Columns\TextColumn::make('drug_name')->label(__('dashboard.drug_name')),
                Tables\Columns\TextColumn::make('doctor.name')->label(__('dashboard.doctor_name')),
                Tables\Columns\TextColumn::make('frequency')->label(__('dashboard.frequency')),
                Tables\Columns\TextColumn::make('frequency_per')->label(__('dashboard.frequency_per')),
            ])
            ->filters([
                //
            ])
            // ->headerActions([])
            ->headerActions([
                // Adding the Create button
                Tables\Actions\CreateAction::make() // This will add the create button
                    ->label(__('dashboard.add_patient_medicament')) // You can customize the label here
                    ->icon('heroicon-o-plus') // Optional: add an icon for the button
            ])
            ->actions([
                // This array will conditionally include actions based on hospital_id
                Tables\Actions\EditAction::make()->hidden(fn($record) =>
                $record->hospital_id != self::getHospitalId()),
                Tables\Actions\DeleteAction::make()->hidden(fn($record) =>
                $record->hospital_id != self::getHospitalId()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            // 'index' => Pages\ListPatients::route('/'),
            // 'create' => Pages\CreateMedication::route('/create'),
            // 'edit' => Pages\AddMedication::route('/{record}/edit'),
        ];
    }
}
