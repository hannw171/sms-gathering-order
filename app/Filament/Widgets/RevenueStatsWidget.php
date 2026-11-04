<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeOrders = Order::where('status', '!=', 'cancelled')->get();
        $totalOrders = $activeOrders->count();
        $totalGMV = (float) $activeOrders->sum('total_amount');
        $aov = $totalOrders > 0 ? ($totalGMV / $totalOrders) : 0.0;

        $totalUnits = (int) OrderItem::whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))->sum('quantity');

        return [
            Stat::make('Total GMV Omset', 'Rp '.number_format($totalGMV, 0, ',', '.'))
                ->description('Seluruh pesanan terkonfirmasi')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Order Diterima', (string) $totalOrders.' PO')
                ->description('Dari transaksi apotek')
                ->color('info')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Rata-rata Order (AOV)', 'Rp '.number_format($aov, 0, ',', '.'))
                ->description('Average order value per PO')
                ->color('warning')
                ->icon('heroicon-o-calculator'),

            Stat::make('Total Volume Terjual', number_format($totalUnits, 0, ',', '.').' Box/Fls')
                ->description('Akumulasi seluruh produk')
                ->color('primary')
                ->icon('heroicon-o-archive-box'),
        ];
    }
}
