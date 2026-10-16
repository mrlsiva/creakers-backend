<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Download Orders')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Download Orders')
                ->modalDescription('Downloads all orders matching the current search and filters.')
                ->modalSubmitActionLabel('Download')
                ->form(OrderResource::downloadForm())
                ->action(fn(array $data) => OrderResource::downloadOrders(
                    $this->getFilteredSortedTableQuery(),
                    $data
                )),
        ];
    }
}
