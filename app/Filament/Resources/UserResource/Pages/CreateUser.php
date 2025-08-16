<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['profession_ar'])) {
            $data['profession_en'] = GoogleTranslate::trans($data['profession_ar'], 'en', 'ar');
        } else {
            $data['profession_ar'] = GoogleTranslate::trans($data['profession_en'], 'ar', 'en');
        }

        return $data;
    }
}
