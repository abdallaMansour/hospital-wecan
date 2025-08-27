<?php

namespace App\Filament\Resources\HealthTipResource\Pages;

use App\Filament\Resources\HealthTipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Stichoza\GoogleTranslate\GoogleTranslate;

class EditHealthTip extends EditRecord
{
    protected static string $resource = HealthTipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure user_id is preserved from the existing record
        $data['user_id'] = $this->record->user_id;
        
        $locale = app()->getLocale();
        
        // Auto-translate title
        if ($locale === 'en' && !empty($data['title_en'])) {
            $data['title_ar'] = GoogleTranslate::trans($data['title_en'], 'ar', 'en');
        } elseif ($locale === 'ar' && !empty($data['title_ar'])) {
            $data['title_en'] = GoogleTranslate::trans($data['title_ar'], 'en', 'ar');
        }
        
        // Auto-translate details
        if ($locale === 'en' && !empty($data['details_en'])) {
            $data['details_ar'] = GoogleTranslate::trans($data['details_en'], 'ar', 'en');
        } elseif ($locale === 'ar' && !empty($data['details_ar'])) {
            $data['details_en'] = GoogleTranslate::trans($data['details_ar'], 'en', 'ar');
        }
        
        return $data;
    }
}
