<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

class EditDoctor extends EditRecord
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Debug: Log the data being filled into the form
        Log::info('EditDoctor mutateFormDataBeforeFill - data:', $data);
        
        // Ensure account_type is properly set
        if (isset($data['account_type'])) {
            Log::info('Account type found in data:', ['account_type' => $data['account_type']]);
        } else {
            Log::warning('Account type not found in data');
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Debug: Log the saved record
        Log::info('EditDoctor afterSave - record saved:', [
            'id' => $this->record->id,
            'account_type' => $this->record->account_type,
            'name' => $this->record->name,
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Debug: Log the incoming data
        Log::info('EditDoctor mutateFormDataBeforeSave - incoming data:', $data);
        
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

        if (empty($data['password'])) {
            unset($data['password']);
        }

        unset($data['profession']);
        unset($data['password_confirmation']);

        // Debug: Log the final data
        Log::info('EditDoctor mutateFormDataBeforeSave - final data:', $data);

        return $data;
    }
}
