<?php

namespace App\Filament\Pages;

use App\Exports\CustomersExport;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class Customers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Customers';
    protected static ?string $title = 'Customers';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.customers';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => Excel::download(
                    new CustomersExport(),
                    'customers_' . now()->format('Y-m-d') . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                )),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->join('sites', 'sites.id', '=', 'orders.site_id')
                    ->selectRaw('
                        MIN(orders.id)              AS id,
                        orders.customer_phone,
                        orders.site_id,
                        sites.name                  AS site_name,
                        MAX(orders.customer_name)   AS customer_name,
                        MAX(orders.customer_email)  AS customer_email,
                        MAX(orders.customer_city)   AS customer_city,
                        MAX(orders.customer_pincode) AS customer_pincode,
                        COUNT(*)                    AS orders_count,
                        SUM(orders.total_amount)    AS total_spent,
                        MAX(orders.created_at)      AS last_order_at
                    ')
                    ->groupBy('orders.customer_phone', 'orders.site_id', 'sites.name')
            )
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('customer_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone copied')
                    ->toggleable(),

                TextColumn::make('site_name')
                    ->label('Site')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->default('—')
                    ->toggleable(),

                TextColumn::make('customer_city')
                    ->label('City')
                    ->default('—')
                    ->toggleable(),

                TextColumn::make('customer_pincode')
                    ->label('Pincode')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->money('INR')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_order_at')
                    ->label('Last Order')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('last_order_at', 'desc')
            ->filters([
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->options(Site::pluck('name', 'id')),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('viewOrders')
                    ->label('Orders')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('gray')
                    ->url(fn($record) => OrderResource::getUrl('index') . '?tableSearch=' . urlencode($record->customer_phone)),
            ]);
    }
}
