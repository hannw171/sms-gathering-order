<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Services\GatheringService;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopSpendersWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $gatheringService = app(GatheringService::class);
        $leaderboard = null;

        return $table
            ->heading('🏆 Top 5 Apotek Belanja Terbesar')
            ->description('Peringkat transaksi tertinggi malam gathering')
            ->headerActions([
                Action::make('view_all')
                    ->label('Lihat Semua Peringkat →')
                    ->url('/admin/leaderboard-report')
                    ->color('primary')
                    ->button()
                    ->size('xs'),
            ])
            ->query(
                Pharmacy::query()
                    ->select('pharmacies.*')
                    ->selectSub(
                        Order::whereColumn('orders.pharmacy_id', 'pharmacies.id')
                            ->where('status', '!=', 'cancelled')
                            ->selectRaw('COALESCE(SUM(total_amount), 0)'),
                        'total_spent'
                    )
                    ->selectSub(
                        Order::whereColumn('orders.pharmacy_id', 'pharmacies.id')
                            ->where('status', '!=', 'cancelled')
                            ->selectRaw('MIN(created_at)'),
                        'first_ordered_at'
                    )
                    ->orderByDesc('total_spent')
                    ->orderBy('first_ordered_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('Rank')
                    ->state(function (Pharmacy $record) use ($gatheringService, &$leaderboard): string {
                        $leaderboard ??= $gatheringService->getLeaderboard();
                        $entry = $leaderboard->firstWhere('id', $record->id);
                        $rank = $entry['rank'] ?? null;
                        if ($entry && $entry['total_spending'] > 0) {
                            return match ($rank) {
                                1 => '🥇 #1',
                                2 => '🥈 #2',
                                3 => '🥉 #3',
                                default => '#'.$rank,
                            };
                        }

                        return '-';
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        if (str_contains($state, '🥇')) {
                            return 'warning';
                        }
                        if (str_contains($state, '🥈')) {
                            return 'gray';
                        }
                        if (str_contains($state, '🥉')) {
                            return 'danger';
                        }

                        return 'gray';
                    })
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Apotek / Klinik')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Pharmacy $record): string => $record->customer_code.' • '.($record->city ?? '-')),

                TextColumn::make('orders_count')
                    ->label('PO')
                    ->state(fn (Pharmacy $record): int => $record->orders()->where('status', '!=', 'cancelled')->count())
                    ->alignCenter(),

                TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->money('IDR', locale: 'id')
                    ->alignEnd()
                    ->weight(FontWeight::Black)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
            ])
            ->emptyStateHeading('Belum Ada Transaksi')
            ->emptyStateDescription('Peringkat apotek diperbarui real-time saat transaksi masuk.')
            ->paginated(false);
    }
}
