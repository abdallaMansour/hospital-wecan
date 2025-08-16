<?php

namespace App\Filament\Resources\HealthTipResource\Widgets;

use App\Models\HealthTip;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class HealthTipOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tips = HealthTip::where('user_id', Auth::id())->get();

        // Initialize counts for each tip type
        $tipTypeCounts = [
            'Medication Tips' => 0,
            'General Tips' => 0,
            'Nutrition Tips' => 0,
            'Dosage Tips' => 0,
            'Other' => 0,
        ];

        // Loop through each tip and increment the count based on its tip type
        foreach ($tips as $tip) {
            $tipTypeCounts[$tip->tip_type]++;
        }

        // Create an array of Stat objects for each tip type count
        $stats = [];
        foreach ($tipTypeCounts as $tipType => $count) {
            $stats[] = Stat::make(__('dashboard.' . strtolower(str_replace(' ', '_', $tipType))), $count);
        }

        return $stats;
    }
}
