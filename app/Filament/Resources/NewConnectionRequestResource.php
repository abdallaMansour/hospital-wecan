<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Tables;
use App\Models\Hospital;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use App\Models\HospitalUserAttachment;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\NewConnectionRequestResource\Pages;

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
        return Auth::user()->hospital_id;
    }

    public static function getQuery()
    {

        $query = HospitalUserAttachment::where('doctor_id', Auth::id())->orWhere('hospital_id', self::getHospitalId());
        return $query;
    }

    public static function getName($record)
    {
        $authentication_type = Auth::user()->account_type;

        if ($authentication_type === 'doctor') {
            if ($record->user_id) {
                $user = User::select('name')->where('id', $record->user_id)->first();
                return $user ? $user->name : 'Unknown 1';
            } else {
                $user = Hospital::find($record->hospital_id)->user;
                return $user ? $user->name : 'Unknown 1';
            }
        } elseif ($authentication_type === 'hospital') {
            if ($record->doctor_id) {
                $user = User::select('name')->where('id', $record->doctor_id)->first();
                return $user ? $user->name : 'Unknown 2';
            } else {
                $user = User::select('name')->where('id', $record->user_id)->first();
                return $user ? $user->name : 'Unknown 2';
            }
        }
    }

    public static function getEmail($record)
    {
        $authentication_type = Auth::user()->account_type;
        if ($authentication_type === 'doctor') {
            if ($record->user_id) {
                $user = User::select('email')->where('id', $record->user_id)->first();
                return $user ? $user->email : 'Unknown 1';
            } else {
                $user = Hospital::find($record->hospital_id)->user;
                return $user ? $user->email : 'Unknown 1';
            }
        } elseif ($authentication_type === 'hospital') {
            if ($record->doctor_id) {
                $user = User::select('email')->where('id', $record->doctor_id)->first();
                return $user ? $user->email : 'Unknown 2';
            } else {
                $user = User::select('email')->where('id', $record->user_id)->first();
                return $user ? $user->email : 'Unknown 2';
            }
        }
    }

    public static function getCountry($record)
    {
        $authentication_type = Auth::user()->account_type;
        if ($authentication_type === 'doctor') {
            if ($record->user_id) {
                $user = User::find($record->user_id);
                return $user ? $user->country?->{'name_' . app()->getLocale()} : 'Unknown 1';
            } else {
                $user = Hospital::find($record->hospital_id)->user;
                return $user ? $user->country?->{'name_' . app()->getLocale()} : 'Unknown 1';
            }
        } elseif ($authentication_type === 'hospital') {
            if ($record->doctor_id) {
                $user = User::find($record->doctor_id);
                return $user ? $user->country?->{'name_' . app()->getLocale()} : 'Unknown 2';
            } else {
                $user = User::find($record->user_id);
                return $user ? $user->country?->{'name_' . app()->getLocale()} : 'Unknown 2';
            }
        }
    }

    public static function getAccountType($record)
    {
        $authentication_type = Auth::user()->account_type;
        if ($authentication_type === 'doctor') {
            if ($record->user_id) {
                $user = User::select('account_type')->where('id', $record->user_id)->first();
                return $user ? $user->account_type : 'Unknown 1';
            } else {
                $user = Hospital::find($record->hospital_id)->user;
                return $user ? $user->account_type : 'Unknown 1';
            }
        } elseif ($authentication_type === 'hospital') {
            if ($record->doctor_id) {
                $user = User::select('account_type')->where('id', $record->doctor_id)->first();
                return $user ? $user->account_type : 'Unknown 2';
            } else {
                $user = User::select('account_type')->where('id', $record->user_id)->first();
                return $user ? $user->account_type : 'Unknown 2';
            }
        }
    }


    public static function table(Table $table): Table
    {
        return $table->query(
            self::getQuery()
        )
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('display_name')->label(__('dashboard.name')),
                TextColumn::make('email')->label(__('dashboard.email')),
                TextColumn::make('country')->label(__('dashboard.country'))->searchable(),

                TextColumn::make('account_type')->label(__('dashboard.account_type'))->badge()
                    ->color(fn(string $state): string => match ($state) {
                        __('dashboard.patient') => 'warning',
                        __('dashboard.doctor') => 'info',
                        __('dashboard.hospital') => 'success',
                        default => 'gray',
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
                        // Select::make('user_id')
                        //     ->label(__('dashboard.user'))
                        //     ->options(User::where('account_type', 'patient')->get()->pluck('email', 'id'))
                        //     ->searchable()
                        //     ->required()
                        //     ->reactive(),
                        TextInput::make('email')
                            ->label(__('dashboard.email'))
                            ->required()
                            ->email(),
                        Select::make('status')
                            ->options([
                                'pending' => __('dashboard.pending'),
                            ])
                            ->default('pending')
                            ->hidden()
                            ->required(),
                    ])
                    ->using(function (array $data, $model) {
                        $user = User::where('email', $data['email'])->first();

                        if (!$user) {
                            return;
                        }

                        if ($user->account_type !== 'patient' && $user->account_type !== 'doctor' && $user->account_type !== 'hospital') {
                            return;
                        }

                        $authentication_type = Auth::user()->account_type;

                        if ($user->account_type === 'hospital' && $authentication_type === 'doctor') {
                            $data['hospital_id'] = $user->hospital_id;
                            $data['doctor_id'] = Auth::id();
                        } elseif ($user->account_type === 'doctor' && $authentication_type === 'hospital') {
                            $data['doctor_id'] = $user->id;
                            $data['hospital_id'] = Auth::user()->hospital_id;
                        } elseif ($user->account_type === 'patient' && $authentication_type === 'hospital') {
                            $data['hospital_id'] = Auth::user()->hospital_id;
                            $data['user_id'] = $user->id;
                        } elseif ($user->account_type === 'patient' && $authentication_type === 'doctor') {
                            $data['user_id'] = $user->id;
                            $data['doctor_id'] = Auth::id();
                        }

                        $data['account_type'] = $authentication_type;
                        $data['sender_id'] = Auth::id();

                        unset($data['email']);

                        return $model::create($data);
                    })

            ])

            // form email status and custom action
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make('cancel')
                    ->label(__('dashboard.unlink'))
                    ->modalHeading(__(key: 'dashboard.unlink_doctor'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        if ($record->user_id)
                            $user = User::find($record->user_id);
                        else if ($record->doctor_id && $record->doctor_id !== Auth::id())
                            $user = User::find($record->doctor_id);
                        else if ($record->hospital_id && $record->hospital_id !== Auth::user()->hospital_id)
                            $user = Hospital::find($record->hospital_id)->user;

                        if ($user)
                            $user->update(['parent_id' => NULL]);

                        $record->delete();

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
            'edit' => Pages\EditStatusConnectionRequest::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        // return ['name', 'email'];
        return [];
    }
}
