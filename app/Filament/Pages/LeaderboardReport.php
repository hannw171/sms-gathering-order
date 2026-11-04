<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LeaderboardStatsWidget;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Services\GatheringService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class LeaderboardReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static UnitEnum|string|null $navigationGroup = 'Laporan Gathering';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected string $view = 'filament.pages.leaderboard-report';

    public function getTitle(): string|Htmlable
    {
        return 'Rekapitulasi Peringkat Transaksi Apotek (Terbesar ke Terkecil)';
    }

    public static function getNavigationLabel(): string
    {
        return 'Peringkat Apotek';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LeaderboardStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Download Excel / CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportCsv'),
            Action::make('print')
                ->label('Cetak Peringkat A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes(['onclick' => 'window.print()']),
        ];
    }

    public function table(Table $table): Table
    {
        $gatheringService = app(GatheringService::class);

        return $table
            ->description('Urutan peringkat apotek berdasarkan akumulasi transaksi belanja dari terbesar sampai terkecil. Jika nominal transaksi sama, peringkat diprioritaskan kepada transaksi pertama (tie-breaker).')
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
                    ->selectSub(
                        Order::whereColumn('orders.pharmacy_id', 'pharmacies.id')
                            ->where('status', '!=', 'cancelled')
                            ->selectRaw('MAX(created_at)'),
                        'last_ordered_at'
                    )
                    ->orderByDesc('total_spent')
                    ->orderBy('first_ordered_at')
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('Peringkat')
                    ->state(function (Pharmacy $record) use ($gatheringService): string {
                        $leaderboard = $gatheringService->getLeaderboard();
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
                    ->alignCenter()
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
                        if ($state === '-') {
                            return 'gray';
                        }

                        return 'primary';
                    }),

                TextColumn::make('customer_code')
                    ->label('Kode')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Nama Apotek / Klinik')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Pharmacy $record): string => $record->city ?? '-'),

                IconColumn::make('is_checked_in')
                    ->label('Kehadiran')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('orders_count')
                    ->label('Jumlah PO')
                    ->state(fn (Pharmacy $record): int => $record->orders()->where('status', '!=', 'cancelled')->count())
                    ->alignCenter(),

                TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->money('IDR', locale: 'id')
                    ->alignEnd()
                    ->weight(FontWeight::Black)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('first_ordered_at')
                    ->label('Order Pertama (Tie-Breaker)')
                    ->dateTime('H:i:s')
                    ->placeholder('-')
                    ->alignCenter()
                    ->fontFamily(FontFamily::Mono),

                TextColumn::make('last_ordered_at')
                    ->label('Order Terakhir')
                    ->dateTime('H:i:s')
                    ->placeholder('-')
                    ->alignCenter()
                    ->fontFamily(FontFamily::Mono),
            ])
            ->filters([
                Filter::make('has_orders')
                    ->label('Sudah Belanja Saja')
                    ->query(fn ($query) => $query->whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))),

                Filter::make('checked_in')
                    ->label('Hadir Saja')
                    ->query(fn ($query) => $query->where('is_checked_in', true)),

                SelectFilter::make('city')
                    ->label('Kota / Wilayah')
                    ->options(fn () => Pharmacy::distinct()->whereNotNull('city')->pluck('city', 'city')->toArray()),
            ])
            ->defaultPaginationPageOption(25);
    }

    public function exportCsv(): StreamedResponse
    {
        $gatheringService = app(GatheringService::class);
        $leaderboard = $gatheringService->getLeaderboard();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Peringkat_Apotek_SMS_Gathering_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->streamDownload(function () use ($leaderboard) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Peringkat',
                'Kode Apotek',
                'Nama Apotek / Klinik',
                'Kota / Wilayah',
                'Status Kehadiran',
                'Waktu Check-In',
                'Jumlah PO',
                'Total Nominal Belanja (Rp)',
                'Waktu Transaksi Pertama (Tie-Breaker)',
                'Waktu Transaksi Terakhir',
            ]);

            foreach ($leaderboard as $item) {
                $rankDisplay = $item['total_spending'] > 0 ? $item['rank'] : '-';
                $firstOrderStr = $item['first_order_at'] ? Carbon::parse($item['first_order_at'])->format('d/m/Y H:i:s') : '-';
                $lastOrderStr = $item['last_order_at'] ? Carbon::parse($item['last_order_at'])->format('d/m/Y H:i:s') : '-';
                $checkinStr = $item['checked_in_at'] ? Carbon::parse($item['checked_in_at'])->format('d/m/Y H:i') : '-';

                fputcsv($handle, [
                    $rankDisplay,
                    $item['customer_code'],
                    $item['name'],
                    $item['city'] ?? '-',
                    $item['is_checked_in'] ? 'Hadir' : 'Belum Hadir',
                    $checkinStr,
                    $item['orders_count'],
                    number_format($item['total_spending'], 0, ',', '.'),
                    $firstOrderStr,
                    $lastOrderStr,
                ]);
            }

            fclose($handle);
        }, 'Peringkat_Apotek_SMS_Gathering.csv', $headers);
    }
}
