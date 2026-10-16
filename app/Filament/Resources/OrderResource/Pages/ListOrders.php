<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Exports\OrdersExport;
use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

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
                ->form([
                    Radio::make('format')
                        ->label('What to include')
                        ->options([
                            'summary' => 'Order details only (one row per order)',
                            'items'   => 'Order details with products (one row per product)',
                        ])
                        ->default('summary')
                        ->required(),
                ])
                ->action(fn(array $data) => Excel::download(
                    new OrdersExport($this->getFilteredSortedTableQuery(), $data['format'] === 'items'),
                    'orders_' . now()->format('Y-m-d') . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                )),
        ];
    }
}
