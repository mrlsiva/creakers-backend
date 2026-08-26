<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopProductsWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top Selling Products';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $siteId = $this->filters['site_id'] ?? null;

        $rows = cache()->remember("widget_top_products_" . ($siteId ?? 'all'), 60, function () use ($siteId) {
            return OrderItem::selectRaw('product_name, SUM(subtotal) as total')
                ->when($siteId, fn ($q) => $q->whereHas('order', fn ($q) => $q->where('site_id', $siteId)))
                ->groupBy('product_name')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'product_name');
        });

        $palette = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $rows->values()->map(fn ($v) => (float) $v)->all(),
                    'backgroundColor' => array_slice($palette, 0, max($rows->count(), 1)),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $rows->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
