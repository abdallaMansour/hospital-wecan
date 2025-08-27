<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Country;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Auth;

class Profile extends EditProfile
{

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $title = 'Profile';

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Section::make(__('dashboard.profile_picture'))
                    ->schema([
                        FileUpload::make('profile_picture')
                            ->label(__('dashboard.profile_picture'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->visibility('public')
                            ->disk('public')
                            ->directory('profile_pictures')
                            ->columnSpan('full'),
                    ])
                    ->columns(1),

                Section::make(__('dashboard.user_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('dashboard.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('dashboard.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('preferred_language')
                            ->label(__('dashboard.preferred_language'))
                            ->options([
                                'ar' => 'العربية',
                                'en' => 'English',
                            ])
                            ->required(),

                        // Doctor-specific fields
                        TextInput::make('profession_ar')
                            ->label(__('dashboard.profession_ar'))
                            ->maxLength(255)
                            ->visible(fn() => Auth::user() && (Auth::user()->account_type === 'doctor' || Auth::user()->account_type === 'hospital')),

                        TextInput::make('profession_en')
                            ->label(__('dashboard.profession_en'))
                            ->maxLength(255)
                            ->visible(fn() => Auth::user() && (Auth::user()->account_type === 'doctor' || Auth::user()->account_type === 'hospital')),

                        TextInput::make('hospital_ar')
                            ->label(__('dashboard.hospital_ar'))
                            ->maxLength(255)
                            ->visible(fn() => Auth::user() && Auth::user()->account_type === 'hospital'),

                        TextInput::make('hospital_en')
                            ->label(__('dashboard.hospital_en'))
                            ->maxLength(255)
                            ->visible(fn() => Auth::user() && Auth::user()->account_type === 'hospital'),

                        TextInput::make('contact_number')
                            ->label(__('dashboard.contact_number'))
                            ->maxLength(255)
                            ->visible(fn() => Auth::user() && (Auth::user()->account_type === 'doctor' || Auth::user()->account_type === 'hospital')),

                        TextInput::make('experience_years')
                            ->label(__('dashboard.experience_years'))
                            ->numeric()
                            ->visible(fn() => Auth::user() && (Auth::user()->account_type === 'doctor' || Auth::user()->account_type === 'hospital')),

                        // Patient-specific fields
                        Select::make('country_id')
                            ->label(__('dashboard.country'))
                            ->options(Country::all()->pluck('name_' . app()->getLocale(), 'id'))
                            ->searchable()
                            ->visible(fn() => Auth::user() && Auth::user()->account_type === 'patient'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = Auth::user();
        
        $data['profession_ar'] = $user->profession_ar;
        $data['profession_en'] = $user->profession_en;
        $data['hospital_ar'] = $user->hospital_ar;
        $data['hospital_en'] = $user->hospital_en;
        $data['contact_number'] = $user->contact_number;
        $data['experience_years'] = $user->experience_years;
        $data['country_id'] = $user->country_id;
        $data['preferred_language'] = $user->preferred_language;
        $data['show_info_to_patients'] = $user->show_info_to_patients;
        $data['profile_picture'] = $user->profile_picture;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        // Update custom fields
        $user->profession_ar = $data['profession_ar'] ?? null;
        $user->profession_en = $data['profession_en'] ?? null;
        $user->hospital_ar = $data['hospital_ar'] ?? null;
        $user->hospital_en = $data['hospital_en'] ?? null;
        $user->contact_number = $data['contact_number'] ?? null;
        $user->experience_years = $data['experience_years'] ?? null;
        $user->country_id = $data['country_id'] ?? null;
        $user->preferred_language = $data['preferred_language'] ?? 'ar';
        $user->show_info_to_patients = $data['show_info_to_patients'] ?? true;
        
        if (!empty($data['profile_picture'])) {
            $user->profile_picture = $data['profile_picture'];
        }

        $user->save();

        return $data;
    }
}
