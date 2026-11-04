<?php

namespace App\Filament\Widgets;

use App\Services\RewardCalculationService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RewardRecapStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $rewardService = app(RewardCalculationService::class);
        $summaries = $rewardService->getAllPharmaciesRewardsSummary();

        $totalPharmacies = $summaries->count();
        $withRewards = $summaries->where('has_reward', true)->count();
        $totalVouchers = $summaries->sum(fn ($i) => $i['rewards']['vouchers'] ?? 0);
        $handedOver = $summaries->where('is_handed_over', true)->count();
        $pending = $summaries->where('has_reward', true)->where('is_handed_over', false)->count();

        return [
            Stat::make('Total Apotek', (string) $totalPharmacies)
                ->description('Terdaftar di Gathering')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('Berhak Hadiah', (string) $withRewards)
                ->description('Memenuhi syarat reward')
                ->color('warning')
                ->icon('heroicon-o-gift'),

            Stat::make('Total Voucher', 'Rp '.number_format($totalVouchers, 0, ',', '.'))
                ->description('Akumulasi seluruh pabrik')
                ->color('success')
                ->icon('heroicon-o-ticket'),

            Stat::make('Sudah Diserahkan', (string) $handedOver)
                ->description('Telah serah terima')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Belum Diserahkan', (string) $pending)
                ->description('Menunggu penyerahan')
                ->color('danger')
                ->icon('heroicon-o-clock'),
        ];
    }
}
