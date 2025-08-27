<?php

namespace App\Filament\Resources\HealthTipResource\Pages;

use App\Filament\Resources\HealthTipResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CreateHealthTip extends CreateRecord
{
    protected static string $resource = HealthTipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure user_id is set for new records
        $data['user_id'] = \Illuminate\Support\Facades\Auth::id();
        
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
