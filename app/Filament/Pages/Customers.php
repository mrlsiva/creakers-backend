<?php

namespace App\Filament\Pages;

use App\Exports\CustomersExport;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
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
                Order::selectRaw('
                    MIN(id)              AS id,
                    customer_phone,
                    MAX(customer_name)   AS customer_name,
                    MAX(customer_email)  AS customer_email,
                    MAX(customer_city)   AS customer_city,
                    MAX(customer_pincode) AS customer_pincode,
                    COUNT(*)             AS orders_count,
                    SUM(total_amount)    AS total_spent,
                    MAX(created_at)      AS last_order_at
                ')
                ->groupBy('customer_phone')
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
            ->actions([
                \Filament\Tables\Actions\Action::make('viewOrders')
                    ->label('Orders')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('gray')
                    ->url(fn($record) => OrderResource::getUrl('index') . '?tableSearch=' . urlencode($record->customer_phone)),
            ]);
    }
}
