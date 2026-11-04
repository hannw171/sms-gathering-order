<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Services\RewardCalculationService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeOrders = Order::where('status', '!=', 'cancelled')->get();
        $totalOrders = $activeOrders->count();
        $totalGMV = (float) $activeOrders->sum('total_amount');
        $aov = $totalOrders > 0 ? ($totalGMV / $totalOrders) : 0.0;

        $totalPharmacies = Pharmacy::count();
        $activePharmacies = Pharmacy::whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))->count();
        $checkedInCount = Pharmacy::where('is_checked_in', true)->count();

        $rewardService = app(RewardCalculationService::class);
        $summaries = $rewardService->getAllPharmaciesRewardsSummary();
        $totalVouchers = $summaries->sum(fn ($i) => $i['rewards']['total_vouchers'] ?? 0);
        $withRewards = $summaries->where('has_reward', true)->count();

        return [
            Stat::make('Total Omset Gathering', 'Rp '.number_format($totalGMV, 0, ',', '.'))
                ->description('Akumulasi seluruh PO terkonfirmasi')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Order Diterima', (string) $totalOrders.' PO')
                ->description('Rata-rata Rp '.number_format($aov, 0, ',', '.').' / PO')
                ->color('primary')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Partisipasi Apotek', (string) $activePharmacies.' / '.$totalPharmacies.' Apotek')
                ->description($checkedInCount.' apotek telah check-in hadir')
                ->color('info')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('Voucher & Hadiah Terkumpul', 'Rp '.number_format($totalVouchers, 0, ',', '.'))
                ->description($withRewards.' apotek telah berhak hadiah')
                ->color('warning')
                ->icon('heroicon-o-gift'),
        ];
    }
}
