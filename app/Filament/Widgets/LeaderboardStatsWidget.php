<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Pharmacy;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeaderboardStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalPharmacies = Pharmacy::count();
        $checkedInCount = Pharmacy::where('is_checked_in', true)->count();

        $activeOrders = Order::where('status', '!=', 'cancelled')->get();
        $totalSpending = (float) $activeOrders->sum('total_amount');
        $orderedCount = Pharmacy::whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))->count();

        return [
            Stat::make('Total Apotek Terdaftar', (string) $totalPharmacies.' Apotek')
                ->description('Peserta Gathering 2026')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('Sudah Bertransaksi', (string) $orderedCount.' Apotek')
                ->description('Telah melakukan order PO')
                ->color('success')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total Omset Belanja', 'Rp '.number_format($totalSpending, 0, ',', '.'))
                ->description('Akumulasi seluruh apotek')
                ->color('primary')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Kehadiran Tamu', (string) $checkedInCount.' Apotek')
                ->description('Status check-in gathering')
                ->color('info')
                ->icon('heroicon-o-check-badge'),
        ];
    }
}
