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
use Filament\Forms\Components\Section;

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
        $authAccount = self::authAccount();
        $authAccountType = self::authAccountType();
        
        if ($authAccountType === 'hospital') {
            $query = HospitalUserAttachment::where('hospital_id', $authAccount->hospital_id);
        } elseif ($authAccountType === 'doctor') {
            $query = HospitalUserAttachment::where('doctor_id', $authAccount->id);
        } else {
            // For users, show records where they are the sender or the target
            $query = HospitalUserAttachment::where(function ($q) use ($authAccount) {
                $q->where('sender_id', $authAccount->id)
                  ->orWhere('user_id', $authAccount->id)
                  ->orWhere('doctor_id', $authAccount->id);
            });
        }
        
        return $query;
    }

    public static function authAccount()
    {
        $user = Auth::user();
        Log::info('authAccount method', [
            'user_id' => $user->id,
            'account_type' => $user->account_type,
            'parent_id' => $user->parent_id,
            'parent_exists' => $user->parent ? true : false
        ]);
        
        if ($user->account_type === 'user') {
            return $user->parent ?? $user;
        }
        return $user;
    }

    public static function authAccountType()
    {
        return self::authAccount()->account_type;
    }

    public static function authParentId()
    {
        return self::authAccount()->id;
    }

    public static function table(Table $table): Table
    {
        $query = self::getQuery();
        
        // Log the query for debugging
        Log::info('NewConnectionRequest table query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'auth_user_id' => Auth::id(),
            'auth_account_type' => self::authAccountType(),
            'auth_account_id' => self::authAccount()->id
        ]);
        
        return $table->query($query)
            ->recordUrl(fn ($record) => null) // Disable record URL to prevent navigation issues
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
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


                        $authentication_type = self::authAccountType();

                        if ($authentication_type === 'hospital') {
                            if ($user->account_type !== 'patient' && $user->account_type !== 'doctor') {
                                return;
                            }
                        } else {
                            if ($user->account_type !== 'patient' && $user->account_type !== 'hospital') {
                                return;
                            }
                        }

                        if ($user->account_type === 'hospital' && $authentication_type === 'doctor') {
                            $data['hospital_id'] = $user->hospital_id;
                            $data['doctor_id'] = self::authAccount()->id;
                        } elseif ($user->account_type === 'doctor' && $authentication_type === 'hospital') {
                            $data['doctor_id'] = $user->id;
                            $data['hospital_id'] = self::authAccount()->hospital_id;
                        } elseif ($user->account_type === 'patient' && $authentication_type === 'hospital') {
                            $data['hospital_id'] = self::authAccount()->hospital_id;
                            $data['user_id'] = $user->id;
                        } elseif ($user->account_type === 'patient' && $authentication_type === 'doctor') {
                            $data['user_id'] = $user->id;
                            $data['doctor_id'] = self::authAccount()->id;
                        }

                        $data['account_type'] = $authentication_type;
                        $data['sender_id'] = self::authAccount()->id;

                        unset($data['email']);

                        return $model::create($data);
                    })

            ])

            // form email status and custom action
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(function ($record) {
                        $shouldHide = (self::authAccountType() === 'user' ? $record->sender_id === self::authParentId() : $record->sender_id === self::authAccount()->id);
                        
                        // Log for debugging
                        Log::info('EditAction hidden check', [
                            'record_id' => $record->id,
                            'record_sender_id' => $record->sender_id,
                            'auth_account_type' => self::authAccountType(),
                            'auth_parent_id' => self::authParentId(),
                            'auth_account_id' => self::authAccount()->id,
                            'should_hide' => $shouldHide
                        ]);
                        
                        return $shouldHide;
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
                        // Ensure we have the correct record by re-fetching it
                        $recordId = $record->id;
                        $actualRecord = HospitalUserAttachment::find($recordId);
                        
                        if (!$actualRecord) {
                            Log::error('Record not found', ['record_id' => $recordId]);
                            return;
                        }
                        
                        // Log the record ID for debugging
                        Log::info('Updating HospitalUserAttachment record', [
                            'record_id' => $actualRecord->id,
                            'record_data' => $actualRecord->toArray(),
                            'new_status' => $data['status'],
                            'original_record_id' => $record->id
                        ]);

                        $actualRecord->update($data);

                        $authentication_type = self::authAccountType();

                        $user = null;

                        if ($authentication_type === 'doctor') {
                            if ($actualRecord->user_id) {
                                $user = User::find($actualRecord->user_id);
                            } else {
                                $user = Hospital::find($actualRecord->hospital_id)->user;
                            }
                        } elseif ($authentication_type === 'hospital') {
                            if ($actualRecord->doctor_id) {
                                $user = User::find($actualRecord->doctor_id);
                            } else {
                                $user = User::find($actualRecord->user_id);
                            }
                        }

                        if ($data['status'] === 'approved' && $user) {
                            $user->update(['parent_id' => self::authParentId()]);
                        } else if ($user) {
                            $user->update(['parent_id' => NULL]);
                        }

                        // Log the result
                        Log::info('HospitalUserAttachment update completed', [
                            'record_id' => $actualRecord->id,
                            'user_updated' => $user ? $user->id : null
                        ]);
                    })
                    ->modalButton(__('dashboard.save')),
                Tables\Actions\DeleteAction::make('cancel')
                    ->label(__('dashboard.unlink'))
                    ->modalHeading(__(key: 'dashboard.unlink_doctor'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Ensure we have the correct record by re-fetching it
                        $recordId = $record->id;
                        $actualRecord = HospitalUserAttachment::find($recordId);
                        
                        if (!$actualRecord) {
                            Log::error('Record not found for deletion', ['record_id' => $recordId]);
                            return;
                        }

                        // get related user
                        $auth_account_type = self::authAccountType();

                        if ($actualRecord->doctor_id && $actualRecord->user_id) {
                            $user = User::find($actualRecord->user_id);
                        } else if ($actualRecord->doctor_id && $actualRecord->hospital_id) {
                            $user = $auth_account_type == 'doctor' ? Hospital::find($actualRecord->hospital_id)->user : User::find($actualRecord->doctor_id);
                        } else if ($actualRecord->hospital_id && $actualRecord->user_id) {
                            $user = $auth_account_type == 'hospital' ? User::find($actualRecord->user_id) : Hospital::find($actualRecord->hospital_id)->user;
                        }

                        if ($user) {
                            $user->update(['parent_id' => NULL]);
                        }

                        $actualRecord->delete();
                        
                        Log::info('HospitalUserAttachment deleted', [
                            'record_id' => $actualRecord->id,
                            'user_updated' => $user ? $user->id : null
                        ]);
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
