<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;

use App\Models\Country;
use App\Models\User;
use App\Models\Hospital;
use App\Models\HospitalUserAttachment;
use Filament\Forms;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 4;
    public static function create($request)
    {
        abort(403); // Prevents access to the "Add" page
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard.patients');
    }



    public static function getQuery()
    {
        // $user_ids = HospitalUserAttachment::where('hospital_id', self::getHospitalId())
        // ->where('status', 'approved')
        // ->pluck('user_id');
        // User::whereIn('id', $user_ids)->where('account_type', 'patient');
        $query =
            User::select('users.id', 'users.name', 'users.email', 'users.country_id')
            ->where('account_type', 'patient')
            ->join('hospital_user_attachments', function ($join) {
                $join->on('users.id', '=', 'hospital_user_attachments.user_id');
            })
            ->where('hospital_user_attachments.hospital_id', self::getHospitalId())
            ->where('hospital_user_attachments.status', 'approved');

        // dd($query->toSql());
        return $query;
    }




    public static function getPluralModelLabel(): string
    {
        return __('dashboard.patients');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.patient');
    }

    public static function getHospitalId()
    {
        $currentUser = User::find(auth()->user()->id);
        return $currentUser->hospital_id;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }




    public static function table(Table $table): Table
    {
        return $table->query(
            self::getQuery()
        )
            ->columns([
                TextColumn::make('name')
                    ->label(__('dashboard.name'))
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('dashboard.email'))
                    ->sortable(),
                TextColumn::make('country.name_ar')
                    ->label(__('dashboard.country')),
            ])
            ->actions(actions: [
                Tables\Actions\ViewAction::make('show')
                    ->label(__('dashboard.view')),
                Tables\Actions\EditAction::make('edit')
                    ->label(__('dashboard.edit'))
                    ->modalHeading(__('dashboard.edit'))
                    ->color('primary')
                    ->action(function ($record) {
                        return redirect()->route('patient.edit', $record->id);
                    }),
                Tables\Actions\DeleteAction::make('cancel')
                    ->label(__('dashboard.unlink'))
                    ->modalHeading(__(key: 'dashboard.unlink_patient'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        HospitalUserAttachment::where('user_id', $record->id)
                            ->where('hospital_id', $record->hospital_id)
                            ->delete();
                        User::find($record->id)->update(['hospital_id' => NULL]);
                    })
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
            'index' => Pages\ListPatients::route('/'),
            // 'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
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







    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
