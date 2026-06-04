<?php

namespace App\Filament\Resources\SafetyTipResource\Pages;

use App\Filament\Resources\SafetyTipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSafetyTips extends ListRecords
{
    protected static string $resource = SafetyTipResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
