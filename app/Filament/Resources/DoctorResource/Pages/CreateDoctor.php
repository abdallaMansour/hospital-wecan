<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use Filament\Resources\Pages\CreateRecord;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['name_en'])) {
            $data['name'] = GoogleTranslate::trans($data['name_en'], 'ar', 'en');
        } else {
            $data['name_en'] = GoogleTranslate::trans($data['name'], 'en', 'ar');
        }

        if (!empty($data['profession_en'])) {
            $data['profession_ar'] = GoogleTranslate::trans($data['profession_en'], 'ar', 'en');
        } else {
            $data['profession_en'] = GoogleTranslate::trans($data['profession_ar'], 'en', 'ar');
        }

        unset($data['profession']);
        unset($data['password_confirmation']);

        return $data;
    }
}
