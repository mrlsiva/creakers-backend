<?php

namespace App\Filament\Resources\ClientLogoResource\Pages;

use App\Filament\Resources\ClientLogoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientLogos extends ListRecords
{
    protected static string $resource = ClientLogoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
