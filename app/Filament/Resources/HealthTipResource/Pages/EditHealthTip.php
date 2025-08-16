<?php

namespace App\Filament\Resources\HealthTipResource\Pages;

use App\Filament\Resources\HealthTipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHealthTip extends EditRecord
{
    protected static string $resource = HealthTipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
