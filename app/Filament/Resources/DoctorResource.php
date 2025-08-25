<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Country;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Image;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Models\HospitalUserAttachment;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;

class DoctorResource extends Resource
{
    protected static ?string $model = HospitalUserAttachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 4;
    public static function getNavigationLabel(): string
    {
        return __('dashboard.connections');
    }


    public static function getHospitalId()
    {
        return Auth::user()->hospital_id;
    }

    public static function getQuery()
    {
        if (Auth::user()->account_type === 'hospital') {
            $query = HospitalUserAttachment::where('status', 'approved')->where('hospital_id', Auth::user()->hospital_id);
        } elseif (Auth::user()->account_type === 'doctor') {
            $query = HospitalUserAttachment::where('status', 'approved')->where('doctor_id', Auth::user()->id);
        } else {
            $parent_id = Auth::user()->parent->account_type === 'hospital' ? Auth::user()->parent->hospital_id : Auth::user()->parent_id;
            $query = HospitalUserAttachment::where('status', 'approved')->where(Auth::user()->parent?->account_type . '_id', $parent_id);
        }

        return $query->with('doctor', 'hospital.user');
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
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) $record->user_id;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('dashboard.user_information'))
                    ->schema([
                        Forms\Components\Placeholder::make('image')
                            ->label(__('dashboard.profile_picture'))
                            ->content(function ($record) {
                                // $profilePicture = null;

                                // $authentication_type = Auth::user()->account_type;
                                // if ($authentication_type === 'doctor') {
                                //     if ($record->user) {
                                //         $profilePicture = $record->user->profile_picture;
                                //     } elseif ($record->hospital) {
                                //         $profilePicture = $record->hospital->hospital_logo;
                                //     }
                                // } elseif ($authentication_type === 'hospital') {
                                //     if ($record->user) {
                                //         $profilePicture = $record->user->profile_picture;
                                //     } elseif ($record->doctor) {
                                //         $profilePicture = $record->doctor->profile_picture;
                                //     }
                                // }

                                // if ($profilePicture) {
                                //     // check if the image is in the storage folder
                                //     if (Storage::exists($profilePicture)) {
                                //         $profilePicture = 'storage/' . $profilePicture;
                                //     } else {
                                //         $profilePicture = env('ADMIN_DASHBOARD_URL') . '/storage/' . $profilePicture;
                                //     }
                                // }

                                return $record->image
                                    ? new HtmlString('<img src="' . $record->image . '" style="max-width: 100px; height: auto;">')
                                    : __('dashboard.no_image');
                            }),

                        Forms\Components\Placeholder::make('display_name')
                            ->label(__('dashboard.name'))
                            ->content(function ($record) {
                                return $record->display_name;
                                // $locale = app()->getLocale();
                                // $nameField = $locale === 'ar' ? 'name' : 'name_en';

                                // $authentication_type = Auth::user()->account_type;
                                // if ($authentication_type === 'doctor') {
                                //     if ($record->user) {
                                //         return $record->user->$nameField;
                                //     } elseif ($record->hospital) {
                                //         return $record->hospital->user->$nameField;
                                //     }
                                // } elseif ($authentication_type === 'hospital') {
                                //     if ($record->user) {
                                //         return $record->user->$nameField;
                                //     } elseif ($record->doctor) {
                                //         return $record->doctor->$nameField;
                                //     }
                                // }

                                // return __('dashboard.not_available');
                            }),

                        Forms\Components\Placeholder::make('email')
                            ->label(__('dashboard.email'))
                            ->content(function ($record) {
                                return $record->email;
                                // $authentication_type = Auth::user()->account_type;
                                // if ($authentication_type === 'doctor') {
                                //     if ($record->user) {
                                //         return $record->user->email;
                                //     } elseif ($record->hospital) {
                                //         return $record->hospital->user->email;
                                //     }
                                // } elseif ($authentication_type === 'hospital') {
                                //     if ($record->user) {
                                //         return $record->user->email;
                                //     } elseif ($record->doctor) {
                                //         return $record->doctor->email;
                                //     }
                                // }

                                // return __('dashboard.not_available');
                            }),

                        Forms\Components\Placeholder::make('profession')
                            ->label(__('dashboard.profession'))
                            ->content(function ($record) {
                                return $record->profession;
                                // $professionField = 'profession_' . app()->getLocale();

                                // $authentication_type = Auth::user()->account_type;
                                // if ($authentication_type === 'doctor') {
                                //     if ($record->user) {
                                //         return $record->user->$professionField;
                                //     } elseif ($record->hospital) {
                                //         return $record->hospital->user->$professionField;
                                //     }
                                // } elseif ($authentication_type === 'hospital') {
                                //     if ($record->user) {
                                //         return $record->user->$professionField;
                                //     } elseif ($record->doctor) {
                                //         return $record->doctor->$professionField;
                                //     }
                                // }

                                // return __('dashboard.not_available');
                            }),

                        Forms\Components\Placeholder::make('account_type')
                            ->label(__('dashboard.account_type'))
                            ->content(function ($record) {
                                return $record->account_type;
                                // $authentication_type = Auth::user()->account_type;

                                // if ($authentication_type === 'doctor') {
                                //     if ($record->user) {
                                //         return __('dashboard.' . $record->user->account_type);
                                //     } elseif ($record->hospital) {
                                //         return __('dashboard.' . $record->hospital->user->account_type);
                                //     }
                                // } elseif ($authentication_type === 'hospital') {
                                //     if ($record->user) {
                                //         return __('dashboard.' . $record->user->account_type);
                                //     } elseif ($record->doctor) {
                                //         return __('dashboard.' . $record->doctor->account_type);
                                //     }
                                // }

                                // return __('dashboard.not_available');
                            }),

                        Forms\Components\Placeholder::make('contact_number')
                            ->label(__('dashboard.contact_number'))
                            ->content(function ($record) {
                                return $record->contact_number;
                                // $authentication_type = Auth::user()->account_type;
                                // if ($authentication_type === 'doctor') {
                                //     if ($record->user) {
                                //         return $record->user->contact_number;
                                //     } elseif ($record->hospital) {
                                //         return $record->hospital->user->contact_number;
                                //     }
                                // } elseif ($authentication_type === 'hospital') {
                                //     if ($record->user) {
                                //         return $record->user->contact_number;
                                //     } elseif ($record->doctor) {
                                //         return $record->doctor->contact_number;
                                //     }
                                // }

                                // return __('dashboard.not_available');
                            }),

                        Forms\Components\Placeholder::make('country')
                            ->label(__('dashboard.country'))
                            ->content(function ($record) {
                                return $record->country;
                                // $authentication_type = Auth::user()->account_type;
                                // if ($authentication_type === 'doctor') {
                                //     if ($record->user) {
                                //         return $record->user->country?->{'name_' . app()->getLocale()};
                                //     } elseif ($record->hospital) {
                                //         return $record->hospital->user->country?->{'name_' . app()->getLocale()};
                                //     }
                                // } elseif ($authentication_type === 'hospital') {
                                //     if ($record->user) {
                                //         return $record->user->country?->{'name_' . app()->getLocale()};
                                //     } elseif ($record->doctor) {
                                //         return $record->doctor->country?->{'name_' . app()->getLocale()};
                                //     }
                                // }

                                // return __('dashboard.not_available');
                            }),

                        Forms\Components\Placeholder::make('status')
                            ->label(__('dashboard.status'))
                            ->content(function ($record) {
                                return __('dashboard.' . $record->status);
                            }),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
            ])
            ->columns(2);
    }
    public static function authAccountType()
    {
        if (Auth::user()->account_type === 'user') {
            return Auth::user()->parent?->account_type;
        }

        return Auth::user()->account_type;
    }
    public static function table(Table $table): Table
    {
        $auth_account_type = self::authAccountType();

        if ($auth_account_type === 'hospital') {
            return $table
                ->query(self::getQuery())
                ->columns([
                    Tables\Columns\ImageColumn::make('image')
                        ->label(__('dashboard.image'))
                        ->getStateUsing(function ($record) {
                            if ($record->user && $record->user->account_type === 'patient' && $record->user->cancer_id) {
                                return $record->user->cancer?->cancer_image_path;
                            }

                            return $record->image;
                        }),
                    TextColumn::make('display_name')
                        ->label(__('dashboard.name'))
                        // ->getStateUsing(function ($record) {
                        //     $locale = app()->getLocale();
                        //     $nameField = $locale === 'ar' ? 'name' : 'name_en';

                        //     // Check if doctor exists and has the name field
                        //     if ($record->doctor && $record->doctor->$nameField) {
                        //         return $record->doctor->$nameField;
                        //     }

                        //     // Fallback to user name
                        //     if ($record->user && $record->user->$nameField) {
                        //         return $record->user->$nameField;
                        //     }

                        //     return '';
                        // })
                        ->searchable(isIndividual: true)
                        ->sortable(),
                    TextColumn::make('email')
                        ->label(__('dashboard.email'))
                        // ->getStateUsing(function ($record) {
                        //     if ($record->doctor && $record->doctor->email) {
                        //         return $record->doctor->email;
                        //     }

                        //     if ($record->user && $record->user->email) {
                        //         return $record->user->email;
                        //     }

                        //     return '';
                        // })
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->sortable(),
                    TextColumn::make('profession')
                        ->label(__('dashboard.profession_' . app()->getLocale()))
                        // ->getStateUsing(function ($record) {
                        //     $professionField = 'profession_' . app()->getLocale();

                        //     if ($record->doctor && $record->doctor->$professionField) {
                        //         return $record->doctor->$professionField;
                        //     }

                        //     if ($record->user && $record->user->$professionField) {
                        //         return $record->user->$professionField;
                        //     }

                        //     return '';
                        // })
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->sortable(),
                    TextColumn::make('account_type')
                        ->label(__('dashboard.account_type'))
                        ->badge()
                        // ->getStateUsing(function ($record): string {
                        //     $accountType = null;

                        //     if ($record->doctor && $record->doctor->account_type) {
                        //         $accountType = $record->doctor->account_type;
                        //     } elseif ($record->user && $record->user->account_type) {
                        //         $accountType = $record->user->account_type;
                        //     }

                        //     return $accountType ? (string) __('dashboard.' . $accountType) : '';
                        // })
                        ->color(fn(string $state): string => match ($state) {
                            __('dashboard.doctor') => 'info',
                            __('dashboard.patient') => 'warning',
                            __('dashboard.hospital') => 'success',
                            __('dashboard.user') => 'danger',
                            default => 'gray',
                        }),
                ])
                ->filters([])
                ->actions([
                    Tables\Actions\Action::make('chat')
                        ->label(__('dashboard.chat'))
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->url(fn(HospitalUserAttachment $record): string => '/custom-chat?' . ($record->doctor_id ? 'other_doctor_id=' : 'other_user_id=') . ($record->doctor_id ?? $record->user_id)),
                    // ->visible(fn (HospitalUserAttachment $record): bool => $record->doctor_id !== Auth::id()), // i want open custom chat page from here only
                    Tables\Actions\ViewAction::make('show')
                        ->label(__('dashboard.view')),
                ]);
        } else {
            return $table
                ->query(self::getQuery())
                ->columns([
                    Tables\Columns\ImageColumn::make('image')
                        ->label(__('dashboard.image'))
                        ->getStateUsing(function ($record) {
                            if ($record->user && $record->user->account_type === 'patient' && $record->user->cancer_id) {
                                return $record->user->cancer?->cancer_image_path;
                            }

                            return $record->image;
                        }),
                    TextColumn::make('display_name')
                        ->label(__('dashboard.name'))
                        // ->getStateUsing(function ($record) {
                        //     $locale = app()->getLocale();
                        //     $nameField = $locale === 'ar' ? 'name' : 'name_en';

                        //     if ($record->user && $record->user->$nameField) {
                        //         return $record->user->$nameField;
                        //     }

                        //     if ($record->hospital && $record->hospital->user && $record->hospital->user->$nameField) {
                        //         return $record->hospital->user->$nameField;
                        //     }

                        //     return '';
                        // })
                        ->searchable(isIndividual: true)
                        ->sortable(),
                    TextColumn::make('email')
                        ->label(__('dashboard.email'))
                        // ->getStateUsing(function ($record) {
                        //     if ($record->user && $record->user->email) {
                        //         return $record->user->email;
                        //     }

                        //     if ($record->hospital && $record->hospital->user && $record->hospital->user->email) {
                        //         return $record->hospital->user->email;
                        //     }

                        //     return '';
                        // })
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->sortable(),
                    TextColumn::make('profession')
                        ->label(__('dashboard.profession_' . app()->getLocale()))
                        // ->getStateUsing(function ($record) {
                        //     $professionField = 'profession_' . app()->getLocale();

                        //     if ($record->user && $record->user->$professionField) {
                        //         return $record->user->$professionField;
                        //     }

                        //     if ($record->hospital && $record->hospital->user && $record->hospital->user->$professionField) {
                        //         return $record->hospital->user->$professionField;
                        //     }

                        //     return '';
                        // })
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->sortable(),
                    TextColumn::make('account_type')
                        ->label(__('dashboard.account_type'))
                        ->badge()
                        // ->getStateUsing(function ($record): string {
                        //     $accountType = null;

                        //     if ($record->user && $record->user->account_type) {
                        //         $accountType = $record->user->account_type;
                        //     } elseif ($record->hospital && $record->hospital->user && $record->hospital->user->account_type) {
                        //         $accountType = $record->hospital->user->account_type;
                        //     }

                        //     return $accountType ? (string) __('dashboard.' . $accountType) : '';
                        // })
                        ->color(fn(string $state): string => match ($state) {
                            __('dashboard.doctor') => 'info',
                            __('dashboard.patient') => 'warning',
                            __('dashboard.hospital') => 'success',
                            __('dashboard.user') => 'danger',
                            default => 'gray',
                        }),
                ])
                ->filters([])
                ->actions([
                    Tables\Actions\Action::make('chat')
                        ->label(__('dashboard.chat'))
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->url(fn(HospitalUserAttachment $record): string => '/custom-chat?' . ($record->user_id ? 'other_user_id=' : 'other_hospital_id=') . ($record->user_id ?? $record->hospital_id)), // i want open custom chat page from here only
                    // ->visible(fn (HospitalUserAttachment $record): bool => $record->user_id !== Auth::id()),
                    Tables\Actions\ViewAction::make('show')
                        ->label(__('dashboard.view')),
                ]);
        }
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
            'index' => Pages\ListDoctors::route('/'),
            // 'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
