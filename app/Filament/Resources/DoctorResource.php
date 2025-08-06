<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\Country;
use App\Models\HospitalUserAttachment;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Support\Facades\File;

use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DoctorResource extends Resource
{
    protected static ?string $model = HospitalUserAttachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 4;
    public static function create($request)
    {
        abort(403); // Prevents access to the "Add" page
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard.doctors');
    }


    public static function getHospitalId()
    {
        $currentUser = User::find(auth()->user()->id);
        return $currentUser->hospital_id;
    }


    public static function getTableQuery2()
    {
        $query =
            User::select('users.id', 'users.name', 'users.name_en', 'users.email', 'users.country_id')
            ->where('users.account_type', 'doctor')
            ->join('hospital_user_attachments', function ($join) {
                $join->on('users.id', '=', 'hospital_user_attachments.user_id');
            })
            ->where('hospital_user_attachments.hospital_id', self::getHospitalId())
            ->where('hospital_user_attachments.status', 'approved');
        return  $query;
    }

    public static function getQuery()
    {
        return User::select('users.id', 'users.name', 'users.name_en', 'users.email', 'users.country_id', 'users.profile_picture')
            ->join('hospital_user_attachments', function ($join) {
                $join->on('users.id', '=', 'hospital_user_attachments.user_id');
            })
            ->where('users.account_type', 'doctor')
            ->where('hospital_user_attachments.hospital_id', self::getHospitalId())
            ->where('hospital_user_attachments.status', 'approved');
    }




    public static function getPluralModelLabel(): string
    {
        return __('dashboard.doctors');
    }


    public static function getModelLabel(): string
    {
        return __('dashboard.doctor');
    }
    public static function canCreate(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('dashboard.name'))
                            ->maxLength(255)
                            ->readOnly()
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label(__('dashboard.email'))
                            ->required()
                            ->email()
                            ->readOnly()

                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('country_id')
                            ->label(__('dashboard.country'))
                            ->options(Country::all()->pluck('name_ar', 'id'))
                            ->disabled(),
                        // Select::make('account_status')
                        //     ->label(__('dashboard.account_status'))
                        //     ->options([
                        //         'active' => __('dashboard.active'),
                        //         'cancelled' => __('dashboard.cancelled'),
                        //         'banned' => __('dashboard.banned'),
                        //     ])
                        //     ->searchable(),
                        // Forms\Components\TextInput::make('contact_number')
                        //     ->label(__('dashboard.contact_number'))
                        //     ->required()
                        //     ->readOnly()
                        //     ->maxLength(255),


                        FileUpload::make('profile_picture')
                            ->label(__('dashboard.profile_picture'))
                            ->visibility('public')->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->required()
                            ->hidden(fn(?User $record) => $record === null || $record->account_type !== 'doctor'),
                        Toggle::make('show_info_to_patients')
                            ->label(__('dashboard.show_info_to_patients'))
                            ->hidden(fn(?User $record) => $record === null || $record->account_type !== 'doctor'),
                        Forms\Components\Select::make('account_type')
                            ->label(__('dashboard.account_type'))
                            ->options([
                                'hospitalAdmin' => __('dashboard.hospitalAdmin'),
                                'doctor' => __('dashboard.doctor'),
                                'patient' => __('dashboard.patient'),
                                'user' => __('dashboard.user'),
                            ]),

                        // Forms\Components\TextInput::make('password')
                        //     ->type('password')
                        //     ->label(__('dashboard.password'))
                        //     ->required()
                        //     ->maxLength(255),
                        Forms\Components\TextInput::make('hospital_id')
                            ->default(self::getHospitalId())
                            ->readOnly()
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
                TextColumn::make('country.name_ar')
                    ->label(__('dashboard.country')),
                Tables\Columns\TextColumn::make('contact_number')
                    ->label(__('dashboard.phone_number'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make('show')
                    ->label(__('dashboard.view')),
                // Tables\Actions\EditAction::make('edit')
                //     ->label(__('dashboard.edit'))
                //     ->modalHeading(__('dashboard.edit'))
                //     ->color('primary')
                //     ->action(function ($record) {
                //         return redirect()->route('patient.edit', $record->id);
                //     }),
                // custom view
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
            // 'create' => Pages\CreateDoctor::route('/create'),
            // 'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'name_en', 'email'];
    }
}
