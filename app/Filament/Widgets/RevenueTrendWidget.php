<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueTrendWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Revenue Trend (Last 14 Days)';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '260px';
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $siteId = $this->filters['site_id'] ?? null;

        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $totals = cache()->remember("widget_revenue_trend_" . ($siteId ?? 'all'), 60, function () use ($siteId) {
            return Order::selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
                ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
                ->where('created_at', '>=', now()->subDays(13)->startOfDay())
                ->groupBy('day')
                ->pluck('total', 'day');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $days->map(fn ($d) => (float) ($totals[$d] ?? 0))->all(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 3,
                    'pointBackgroundColor' => '#10b981',
                ],
            ],
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
