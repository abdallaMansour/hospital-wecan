<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewConnectionRequestResource\Pages;
use App\Filament\Resources\NewConnectionRequestResource\RelationManagers;
use App\Models\Country;
use App\Models\HospitalUserAttachment;
use App\Models\User;
use App\Models\Hospital;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\File;
use stdClass;

class NewConnectionRequestResource extends Resource
{
    protected static ?string $model = HospitalUserAttachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 4;
    public static function create($request)
    {
        abort(403); // Prevents access to the "Add" page
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard.new_connection_requests');
    }




    public static function getPluralModelLabel(): string
    {
        return __('dashboard.new_connection_requests');
    }

    public static function getHospitalId()
    {
        // $currentUser = User::find(auth()->id());
        // return $currentUser->hospital_id;
        return auth()->user()->hospital_id;
    }

    public static function getQuery()
    {
        $query = User::select(
            'hospital_user_attachments.id as id',
            'hospital_user_attachments.status as status'
        )
            ->join('hospital_user_attachments', function ($join) {
                $join->on('users.hospital_id', '=', 'hospital_user_attachments.hospital_id')
                    ->whereColumn('users.id', 'hospital_user_attachments.user_id');
            })
            ->where(function ($q) {
                $q->where('account_type', 'patient')
                    ->orWhere('account_type', 'doctor');
            })
            ->where(
                'hospital_user_attachments.hospital_id',
                self::getHospitalId()
            );
        $query = HospitalUserAttachment::where('hospital_id', self::getHospitalId());
        // fix the prob
        return $query;
    }

    public static function getName($user_id)
    {
        $user = User::select('name')->where('id', $user_id)->first();
        return $user ? $user->name : 'Unknown';
    }

    public static function getEmail($user_id)
    {
        $user = User::select('email')->where('id', $user_id)->first();
        return $user ? $user->email : 'Unknown';
    }

    public static function getCountry($user_id)
    {
        $user = User::select('country_id')->where('id', $user_id)->with('country')->first();
        return $user && $user->country ? $user->country->name_ar : '';
    }



    public static function getAccountType($user_id)
    {
        $user = User::select('account_type')->where('id', $user_id)->first();
        return $user ? $user->account_type : 'Unknown';
    }






    public static function table(Table $table): Table
    {
        return $table->query(
            self::getQuery()
        )
            ->columns([
                TextColumn::make('name')
                    ->label(__('dashboard.name'))
                    ->getStateUsing(
                        static function ($record): string {
                            return (string) (
                                self::getName($record->user_id)
                            );
                        }
                    ),
                TextColumn::make('email')
                    ->label(__('dashboard.email'))
                    ->getStateUsing(
                        static function ($record): string {
                            return (string) (
                                self::getEmail($record->user_id)
                            );
                        }
                    ),
                TextColumn::make('country')
                    ->label(__('dashboard.country'))
                    ->getStateUsing(
                        static function ($record): string {
                            return (string) (
                                self::getCountry($record->user_id)
                            );
                        }
                    ),

                TextColumn::make('account_type')
                    ->label(__('dashboard.account_type'))
                    ->badge()
                    ->getStateUsing(
                        static function ($record): string {
                            return (string) (
                                self::getAccountType($record->user_id)
                            );
                        }
                    )
                    ->color(fn(string $state): string => match ($state) {
                        'patient' => 'warning',
                        'doctor' => 'info',
                    }),
                TextColumn::make('status')
                    ->label(__('dashboard.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => __('dashboard.pending'),
                        'approved' => __('dashboard.approved'),
                        'rejected' => __('dashboard.rejected'),
                    })
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('dashboard.add_connection_request'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('user_id')
                            ->label(__('dashboard.user'))
                            ->options(User::where('account_type', 'patient')->get()->pluck('email', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive(),
                        // TextInput::make('email')
                        //     ->label('Email')
                        //     ->required()
                        //     ->email(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => __('dashboard.pending'),
                            ])
                            ->default('pending')       // preselect "pending"
                            ->disabled()
                            ->required(),
                    ])
                    ->using(function (array $data, $model) {
                        $user = User::find($data['user_id']);

                        $data['account_type'] = $user->account_type;
                        $data['sender_id'] = auth()->id();
                        $data['hospital_id'] = self::getHospitalId();

                        $user->update(['hospital_id' => self::getHospitalId()]);

                        return $model::create($data);
                    })

            ])

            // form email status and custom action
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn($record) =>
                    $record->sender_id == auth()->id())
                    ->modalHeading(__('dashboard.edit_status'))
                    ->beforeFormFilled(function ($record) {
                        // $filePath = storage_path('app/file.txt');
                        // $content = json_encode([
                        //     'recordId' => $record,
                        // ]);
                        // File::append($filePath, $content);
                    })

                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => __('dashboard.pending'),
                                'approved' => __('dashboard.approved'),
                                'rejected' => __('dashboard.rejected'),
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->update($data);
                    })
                    ->modalButton(__('dashboard.save')),
                Tables\Actions\DeleteAction::make('cancel')
                    ->label(__('dashboard.unlink'))
                    ->modalHeading(__(key: 'dashboard.unlink_doctor'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        User::find($record->user_id)->update(['hospital_id' => NULL]);
                        HospitalUserAttachment::find($record->id)
                            ->delete();

                        // $filePath = storage_path('app/file.txt');
                        // $content = json_encode([
                        //     'recordId' => $record->id,
                        // ]);
                        // File::append($filePath, $content);
                    })
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        $relations = [];

        return $relations;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\NewConnectionRequestList::route('/'),

            // 'create' => Pages\CreateDoctor::route('/create'),
            // 'edit' => Pages\EditStatusConnectionRequest::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        // return ['name', 'email'];
        return [];
    }
}
