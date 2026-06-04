<?php

namespace App\Filament\Resources\SiteContactResource\Pages;

use App\Filament\Resources\SiteContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteContacts extends ListRecords
{
    protected static string $resource = SiteContactResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
