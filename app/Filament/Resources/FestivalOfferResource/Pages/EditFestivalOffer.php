<?php

namespace App\Filament\Resources\FestivalOfferResource\Pages;

use App\Filament\Resources\FestivalOfferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFestivalOffer extends EditRecord
{
    protected static string $resource = FestivalOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
