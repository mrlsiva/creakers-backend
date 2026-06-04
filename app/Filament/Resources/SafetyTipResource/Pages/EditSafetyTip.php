<?php

namespace App\Filament\Resources\SafetyTipResource\Pages;

use App\Filament\Resources\SafetyTipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSafetyTip extends EditRecord
{
    protected static string $resource = SafetyTipResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
