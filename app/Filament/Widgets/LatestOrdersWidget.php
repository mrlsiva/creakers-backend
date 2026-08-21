<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Site;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Orders';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        return ['all' => 'All Sites'] + Site::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::with('site')
                    ->when($this->filter !== 'all', fn($query) => $query->where('site_id', $this->filter))
                    ->latest()
                    ->limit(10)
            )
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
            ->filters([
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->options(fn() => Site::where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Order $record) => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
