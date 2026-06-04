<?php

namespace App\Filament\Resources\ClientLogoResource\Pages;

use App\Filament\Resources\ClientLogoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientLogo extends EditRecord
{
    protected static string $resource = ClientLogoResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
