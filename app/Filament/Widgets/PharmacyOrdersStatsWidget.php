<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Pharmacy;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PharmacyOrdersStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalPharmacies = Pharmacy::count();

        $activeOrders = Order::where('status', '!=', 'cancelled')->get();
        $totalSpending = (float) $activeOrders->sum('total_amount');

        $activePharmacies = Pharmacy::whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))->count();
        $participationRate = $totalPharmacies > 0 ? (($activePharmacies / $totalPharmacies) * 100) : 0.0;
        $avgSpendingPerActive = $activePharmacies > 0 ? ($totalSpending / $activePharmacies) : 0.0;

        return [
            Stat::make('Total Apotek', (string) $totalPharmacies.' Apotek')
                ->description('Terdaftar di gathering')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('Apotek Aktif Belanja', (string) $activePharmacies.' Apotek')
                ->description(number_format($participationRate, 1, ',', '.').'% tingkat partisipasi')
                ->color('success')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Total Omset Belanja', 'Rp '.number_format($totalSpending, 0, ',', '.'))
                ->description('Akumulasi seluruh pesanan')
                ->color('primary')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Rata-rata Belanja Apotek', 'Rp '.number_format($avgSpendingPerActive, 0, ',', '.'))
                ->description('Rata-rata apotek yang order')
                ->color('warning')
                ->icon('heroicon-o-calculator'),
        ];
    }
}
