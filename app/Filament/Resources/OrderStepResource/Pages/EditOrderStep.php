<?php

namespace App\Filament\Resources\OrderStepResource\Pages;

use App\Filament\Resources\OrderStepResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderStep extends EditRecord
{
    protected static string $resource = OrderStepResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
