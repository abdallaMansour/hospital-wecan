<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealthTipResource\Pages;
use App\Models\HealthTip;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class HealthTipResource extends Resource
{
    protected static ?string $model = HealthTip::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';


    public static function getNavigationLabel(): string
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

    public static function canCreate(): bool
    {
        return Auth::user()->account_type === 'doctor' || Auth::user()->account_type === 'admin';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_ar')
                    ->label(__('dashboard.title_ar'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->label(__('dashboard.title_en'))
                    ->maxLength(255),
                Textarea::make('details_ar')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.details_ar')),
                Textarea::make('details_en')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.details_en')),
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
                Hidden::make('user_id')
                    ->default(Auth::id()),
                Toggle::make('visible')
                    ->label(__('dashboard.visible'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(HealthTip::query()->where('user_id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')->label(__('dashboard.title_ar')),
                Tables\Columns\TextColumn::make('publish_datetime')->label(__('dashboard.publish_datetime')),
                ToggleColumn::make('visible')->label(__('dashboard.visible'))
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHealthTips::route('/'),
            'create' => Pages\CreateHealthTip::route('/create'),
            'edit' => Pages\EditHealthTip::route('/{record}/edit'),
        ];
    }
}
