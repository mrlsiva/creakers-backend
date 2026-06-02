<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Orders';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::with('site')->latest()->limit(10))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),

                TextColumn::make('site.name')
                    ->label('Site'),

                TextColumn::make('customer_name')
                    ->label('Customer'),

                TextColumn::make('customer_phone')
                    ->label('Phone'),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('INR'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => Order::statusColor($state)),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A'),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Order $record) => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
