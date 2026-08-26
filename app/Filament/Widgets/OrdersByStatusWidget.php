<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OrdersByStatusWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Orders by Status';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $siteId = $this->filters['site_id'] ?? null;

        [$statuses, $counts] = cache()->remember("widget_orders_by_status_" . ($siteId ?? 'all'), 60, function () use ($siteId) {
            $statuses = OrderStatus::where('is_active', true)->orderBy('sort_order')->get();

            $query = Order::selectRaw('status, COUNT(*) as count')->groupBy('status');
            if ($siteId) {
                $query->where('site_id', $siteId);
            }

            return [$statuses, $query->pluck('count', 'status')];
        });

        $labels = [];
        $data   = [];
        $colors = [];

        $colorMap = [
            'warning' => '#f59e0b',
            'info'    => '#3b82f6',
            'primary' => '#10b981',
            'success' => '#22c55e',
            'danger'  => '#ef4444',
            'gray'    => '#6b7280',
        ];

        foreach ($statuses as $status) {
            $labels[] = $status->name;
            $data[]   = $counts[$status->key] ?? 0;
            $colors[] = $colorMap[$status->color] ?? '#6b7280';
        }

        return [
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => $colors,
                    'hoverOffset'     => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
