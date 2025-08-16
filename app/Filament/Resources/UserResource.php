<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Country;
use App\Models\Hospital;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.users');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Hidden::make('parent_id')
                            ->default(Auth::id())
                            ->required()
                            ->hiddenLabel(),

                        FileUpload::make('profile_picture')
                            ->label(__('dashboard.profile_picture'))
                            ->columnSpan('full')
                            ->visibility('public')->image()
                            ->imageEditor()
                            ->maxSize(2048),

                        Forms\Components\TextInput::make('name')
                            ->label(__('dashboard.name'))
                            ->maxLength(255)
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label(__('dashboard.email'))
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('country_id')
                            ->label(__('dashboard.country'))
                            ->options(Country::all()->pluck('name_' . app()->getLocale(), 'id'))
                            ->searchable()
                            ->hidden(fn(?User $record) => $record === null || $record->account_type !== 'patient'),

                        Select::make('account_status')
                            ->label(__('dashboard.account_status'))
                            ->options([
                                'active' => __('dashboard.active'),
                                'cancelled' => __('dashboard.cancelled'),
                                'banned' => __('dashboard.banned'),
                            ])
                            ->searchable(),
                        Forms\Components\TextInput::make('profession_' . app()->getLocale())
                            ->label(__('dashboard.profession_' . app()->getLocale()))
                            ->required()
                            ->maxLength(255),
                        // Forms\Components\TextInput::make('profession_en')
                        //     ->label(__('dashboard.profession_en'))
                        //     ->required()
                        //     ->maxLength(255),
                        Forms\Components\TextInput::make('contact_number')
                            ->label(__('dashboard.contact_number'))
                            ->required()
                            ->maxLength(255),

                        // Forms\Components\TextInput::make('experience_years')
                        //     ->label(__('dashboard.experience_years'))
                        //     ->required()->numeric(),
                        // FileUpload::make('profile_picture')
                        //     ->label(__('dashboard.profile_picture'))
                        //     ->visibility('public')->image()
                        //     ->imageEditor()
                        //     ->maxSize(2048)
                        //     ->required()
                        //     ->hidden(fn(?User $record) => $record === null || $record->account_type !== 'doctor'),
                        Toggle::make('show_info_to_patients')
                            ->label(__('dashboard.show_info_to_patients'))
                            ->hidden(fn(?User $record) => $record === null || $record->account_type !== 'doctor'),

                        Hidden::make('account_type')
                            ->default('user'),
                        // Forms\Components\Select::make('account_type')
                        //     ->label(__('dashboard.account_type'))
                        //     ->options([
                        //         'hospitalAdmin' => __('dashboard.hospitalAdmin'),
                        //         'doctor' => __('dashboard.doctor'),
                        //         'patient' => __('dashboard.patient'),
                        //         'user' => __('dashboard.user'),
                        //     ])
                        //     ->reactive()
                        //     ->disabled(fn($get) => $get('account_type') === 'user'),

                        Forms\Components\TextInput::make('password')
                            ->type('password')
                            ->label(__('dashboard.password'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hospital_id')
                            ->default(self::getHospitalId())
                            ->extraAttributes(['style' => 'display: none;'])
                            ->hiddenLabel(),

                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn(?User $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('account_type')
                            ->label(__('dashboard.account_type'))
                            ->content(fn(User $record): ?string => $record->account_type),
                        Forms\Components\Placeholder::make('account_type')
                            ->label(__('dashboard.preferred_language'))
                            ->content(fn(User $record): ?string => $record->preferred_language),
                        Forms\Components\Placeholder::make('newsletter_count')
                            ->label(__('dashboard.newsletter_count'))
                            ->content(fn(User $record): ?string => $record->healthTips()->count())
                            ->hidden(fn(?User $record) => $record === null || $record->account_type !== 'doctor'),
                        Forms\Components\Placeholder::make('created_at')
                            ->label(__('dashboard.created_at'))
                            ->content(fn(User $record): ?string => $record->created_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label(__('dashboard.last_modified_at'))
                            ->content(fn(User $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?User $record) => $record === null),
            ])
            ->columns(3);
    }


    public static function getHospitalId()
    {
        return Auth::user()->hospital_id;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::where('parent_id', Auth::id())->where('account_type', 'user')
            )
            ->columns([
                ImageColumn::make('profile_picture')->label(__('dashboard.profile_picture')),
                TextColumn::make('name')
                    ->label(__('dashboard.name'))
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('dashboard.email'))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('country.name_' . app()->getLocale())
                    ->label(__('dashboard.country')),
                TextColumn::make('account_type')
                    ->label(__('dashboard.account_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'patient' => 'warning',
                        'admin' => 'success',
                        'doctor' => 'info',
                        'hospital' => 'danger',
                        'user' => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('contact_number')
                    ->label(__('dashboard.phone_number'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        $relations = [
            RelationManagers\HealthTipsRelationManager::class,
            RelationManagers\PatientMedicationsRelationManager::class,
            RelationManagers\ChemotherapySessionsRelationManager::class,
            RelationManagers\PatientAppointmentsRelationManager::class,
            RelationManagers\PatientFoodsRelationManager::class,
            RelationManagers\PatientHealthReportsRelationManager::class,
            RelationManagers\PatientNotesRelationManager::class,
        ];

        return $relations;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
