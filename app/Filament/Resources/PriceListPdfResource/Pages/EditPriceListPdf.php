<?php

namespace App\Filament\Resources\PriceListPdfResource\Pages;

use App\Filament\Resources\PriceListPdfResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceListPdf extends EditRecord
{
    protected static string $resource = PriceListPdfResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
