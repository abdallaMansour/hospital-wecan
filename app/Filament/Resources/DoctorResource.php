<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Country;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Models\HospitalUserAttachment;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\DoctorResource\Pages;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Model;

class DoctorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 4;
    public static function getNavigationLabel(): string
    {
        return __('dashboard.doctors_and_patients_plural');
    }


    public static function getHospitalId()
    {
        return Auth::user()->hospital_id;
    }


    public static function getTableQuery2()
    {
        $query =
            User::select('users.id', 'users.name', 'users.name_en', 'users.email', 'users.profession_ar', 'users.profession_en', 'users.country_id')
            ->where(function ($q) {
                $q->where('users.account_type', 'doctor')->orWhere('users.account_type', 'patient');
            })
            ->where('users.parent_id', Auth::id());
        return  $query;
    }

    public static function getQuery()
    {
        return User::where(function ($q) {
                $q->where('users.account_type', 'doctor')->orWhere('users.account_type', 'patient');
            })
            ->where('users.parent_id', Auth::id());
    }




    public static function getPluralModelLabel(): string
    {
        return __('dashboard.doctors_and_patients_plural');
    }


    public static function getModelLabel(): string
    {
        return __('dashboard.doctors_and_patients_plural');
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Hidden::make('parent_id')
                            ->default(Auth::id())
                            ->required(),

                        FileUpload::make('profile_picture')
                            ->label(__('dashboard.profile_picture'))
                            ->columnSpan('full')
                            ->visibility('public')
                            ->image()
                            ->imageEditor(),

                        Forms\Components\TextInput::make(app()->getLocale() === 'ar' ? 'name' : 'name_en')
                            ->label(__('dashboard.name'))
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label(__('dashboard.email'))
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('profession_' . app()->getLocale())
                            ->label(__('dashboard.profession_' . app()->getLocale()))
                            ->required()
                            ->maxLength(255),
                        Select::make('country_id')
                            ->required()
                            ->label(__('dashboard.country'))
                            ->options(Country::all()->pluck('name_' . app()->getLocale(), 'id')),

                        Forms\Components\TextInput::make('contact_number')
                            ->label(__('dashboard.contact_number'))
                            ->required()
                            ->maxLength(255),

                        Hidden::make('show_info_to_patients')
                            ->default(true)
                            ->required(),

                        Forms\Components\Select::make('account_type')
                            ->label(__('dashboard.account_type'))
                            ->options([
                                'doctor' => __('dashboard.doctor'),
                                'patient' => __('dashboard.patient'),
                            ])
                            ->required()
                            ->disabled(false)
                            ->dehydrated(true),

                        Forms\Components\TextInput::make('password')
                            ->type('password')
                            ->label(__('dashboard.password'))
                            ->required(fn(?User $record) => $record === null)// nullable on update and set old password
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->type('password')
                            ->label(__('dashboard.password_confirmation'))
                            ->required(fn(?User $record) => $record === null)// nullable on update and set old password
                            ->same('password')
                            ->maxLength(255),

                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn(?User $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('account_type_display')
                            ->label(__('dashboard.account_type'))
                            ->content(fn(User $record): ?string => $record->account_type),
                        Forms\Components\Placeholder::make('preferred_language')
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

    public static function table(Table $table): Table
    {
        return $table
            ->query(self::getQuery())
            ->columns([
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label(__('dashboard.profile_picture')),
                TextColumn::make(app()->getLocale() === 'ar' ? 'name' : 'name_en')
                    ->label(__('dashboard.name'))
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('dashboard.email'))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('profession_' . app()->getLocale())
                    ->label(__('dashboard.profession_' . app()->getLocale()))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('account_type')
                    ->label(__('dashboard.account_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'doctor' => 'info',
                        'patient' => 'warning',
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make('show')
                    ->label(__('dashboard.view')),
                Tables\Actions\EditAction::make('edit')
                    ->label(__('dashboard.edit'))
                    ->modalHeading(__('dashboard.edit'))
                    ->color('primary'),
                Tables\Actions\DeleteAction::make('cancel')
                    ->label(__('dashboard.unlink'))
                    ->modalHeading(__(key: 'dashboard.unlink_doctor'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // HospitalUserAttachment::where('user_id', $record->id)
                        //     ->where('hospital_id', $record->hospital_id)
                        //     ->delete();
                        // User::find($record->id)->update(['hospital_id' => NULL]);
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        $relations = [];

        return $relations;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'name_en', 'email'];
    }
}
