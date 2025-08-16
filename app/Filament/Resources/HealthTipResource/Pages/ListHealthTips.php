<?php

namespace App\Filament\Resources\HealthTipResource\Pages;

use App\Filament\Resources\HealthTipResource;
use App\Filament\Resources\HealthTipResource\Widgets\HealthTipOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListHealthTips extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = HealthTipResource::class;


    protected function getHeaderWidgets(): array
    {
        return [
            HealthTipOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'Medication Tips' => Tab::make(__('dashboard.medication_tips'))->query(fn ($query) => $query->where('user_id', Auth::id())->where('tip_type', 'Medication Tips')),
            'General Tips' => Tab::make(__('dashboard.general_tips'))->query(fn ($query) => $query->where('user_id', Auth::id())->where('tip_type', 'General Tips')),
            'Nutrition Tips' => Tab::make(__('dashboard.nutrition_tips'))->query(fn ($query) => $query->where('user_id', Auth::id())->where('tip_type', 'Nutrition Tips')),
            'Dosage Tips' => Tab::make(__('dashboard.dosage_tips'))->query(fn ($query) => $query->where('user_id', Auth::id())->where('tip_type', 'Dosage Tips')),
            'Other' => Tab::make(__('dashboard.other'))->query(fn ($query) => $query->where('user_id', Auth::id())->where('tip_type', 'Other')),
        ];
    }
}
