<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Site;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $siteId = $this->filters['site_id'] ?? null;

        [$totalOrders, $todayOrders, $totalRevenue, $todayRevenue,
         $totalCustomers, $activeProducts, $activeSites, $pendingOrders] =
            cache()->remember("dashboard_stats_" . ($siteId ?? 'all'), 60, function () use ($siteId) {
                return [
                    Order::when($siteId, fn ($q) => $q->where('site_id', $siteId))->count(),
                    Order::when($siteId, fn ($q) => $q->where('site_id', $siteId))->whereDate('created_at', today())->count(),
                    Order::when($siteId, fn ($q) => $q->where('site_id', $siteId))->sum('total_amount'),
                    Order::when($siteId, fn ($q) => $q->where('site_id', $siteId))->whereDate('created_at', today())->sum('total_amount'),
                    Order::when($siteId, fn ($q) => $q->where('site_id', $siteId))->distinct('customer_phone')->count('customer_phone'),
                    Product::where('is_active', true)
                        ->when($siteId, fn ($q) => $q->whereHas('prices', fn ($q) => $q->where('site_id', $siteId)))
                        ->count(),
                    Site::where('is_active', true)->count(),
                    Order::when($siteId, fn ($q) => $q->where('site_id', $siteId))->where('status', 'pending')->count(),
                ];
            });

        return [
            Stat::make('Total Orders', number_format($totalOrders))
                ->description("Today: {$todayOrders} new")
                ->icon('heroicon-o-shopping-cart')
                ->color('info'),

            Stat::make('Total Revenue', '₹' . number_format($totalRevenue, 2))
                ->description('Today: ₹' . number_format($todayRevenue, 2))
                ->icon('heroicon-o-currency-rupee')
                ->color('success'),

            Stat::make('Pending Orders', number_format($pendingOrders))
                ->description('Awaiting action')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Customers', number_format($totalCustomers))
                ->description('Unique customers')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Active Products', number_format($activeProducts))
                ->description('Listed products')
                ->icon('heroicon-o-cube')
                ->color('info'),

            Stat::make('Active Sites', number_format($activeSites))
                ->description('Running sites')
                ->icon('heroicon-o-globe-alt')
                ->color('success'),
        ];
    }
}
