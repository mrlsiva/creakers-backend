<?php

namespace App\Filament\Resources\PriceListPdfResource\Pages;

use App\Filament\Resources\PriceListPdfResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceListPdfs extends ListRecords
{
    protected static string $resource = PriceListPdfResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
