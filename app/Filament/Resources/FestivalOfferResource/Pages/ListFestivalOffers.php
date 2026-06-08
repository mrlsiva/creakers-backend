<?php

namespace App\Filament\Resources\FestivalOfferResource\Pages;

use App\Filament\Resources\FestivalOfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFestivalOffers extends ListRecords
{
    protected static string $resource = FestivalOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
