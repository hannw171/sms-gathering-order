<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RewardRecapStatsWidget;
use App\Models\Pharmacy;
use App\Services\RewardCalculationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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

class RewardRecapReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static UnitEnum|string|null $navigationGroup = 'Laporan Gathering';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected string $view = 'filament.pages.reward-recap-report';

    public function getTitle(): string|Htmlable
    {
        return 'Rekap Apotek Penerima Hadiah & Voucher';
    }

    public static function getNavigationLabel(): string
    {
        return 'Penerima Hadiah';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RewardRecapStatsWidget::class,
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
                ->label('Cetak Rekapan A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes(['onclick' => 'window.print()']),
        ];
    }

    public function table(Table $table): Table
    {
        $rewardService = app(RewardCalculationService::class);

        return $table
            ->query(Pharmacy::query()->with(['orders.items.product.manufacturer']))
            ->columns([
                TextColumn::make('customer_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->fontFamily(FontFamily::Mono)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Nama Apotek / Klinik')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Pharmacy $record): string => $record->city ?? '-'),

                TextColumn::make('total_spending')
                    ->label('Total Belanja')
                    ->money('IDR', locale: 'id')
                    ->state(fn (Pharmacy $record): float => (float) $record->orders->where('status', '!=', 'cancelled')->sum('total_amount'))
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('physical_rewards')
                    ->label('Hadiah Langsung')
                    ->state(function (Pharmacy $record) use ($rewardService): array {
                        $res = $rewardService->calculateForPharmacy($record);
                        $items = [];
                        foreach ($res['physical_rewards'] ?? [] as $name => $qty) {
                            $items[] = is_numeric($name) ? (string) $qty : "{$name}: {$qty} pcs";
                        }

                        return $items;
                    })
                    ->badge()
                    ->color('warning')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->placeholder('-'),

                TextColumn::make('choice_rewards')
                    ->label('Hadiah Pilihan')
                    ->state(function (Pharmacy $record) use ($rewardService): array {
                        $res = $rewardService->calculateForPharmacy($record);
                        $choices = [];
                        foreach ($res['choice_selections'] ?? [] as $c) {
                            $opt = $c['selected_option'] ?? '';
                            $m = $c['manufacturer_slug'] ?? '';
                            if ($opt) {
                                $choices[] = $m ? "{$opt} ({$m})" : $opt;
                            }
                        }

                        return $choices;
                    })
                    ->badge()
                    ->color('info')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->placeholder('-'),

                TextColumn::make('vouchers')
                    ->label('Total Voucher')
                    ->money('IDR', locale: 'id')
                    ->state(function (Pharmacy $record) use ($rewardService): float {
                        $res = $rewardService->calculateForPharmacy($record);

                        return (float) ($res['total_vouchers'] ?? 0);
                    })
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold),

                IconColumn::make('is_handed_over')
                    ->label('Serah Terima')
                    ->boolean()
                    ->state(fn (Pharmacy $record): bool => ! is_null($record->reward_handed_over_at))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter(),

                TextColumn::make('reward_receiver_name')
                    ->label('Penerima')
                    ->placeholder('-')
                    ->description(fn (Pharmacy $record): ?string => $record->reward_handed_over_at?->format('d/m H:i')),
            ])
            ->recordActions([
                Action::make('handover')
                    ->label('Serahkan')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->button()
                    ->size('xs')
                    ->visible(fn (Pharmacy $record): bool => is_null($record->reward_handed_over_at))
                    ->form([
                        TextInput::make('receiver_name')
                            ->label('Nama Penerima / Perwakilan Apotek')
                            ->default(fn (Pharmacy $record): string => $record->owner_name ?: $record->name)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan Serah Terima (Opsional)'),
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        $record->update([
                            'reward_handed_over_at' => now(),
                            'reward_receiver_name' => $data['receiver_name'],
                            'reward_handover_notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Serah Terima Berhasil')
                            ->body('Hadiah untuk '.$record->name.' telah diserahkan kepada '.$data['receiver_name'])
                            ->success()
                            ->send();
                    }),

                Action::make('cancelHandover')
                    ->label('Batal')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->button()
                    ->size('xs')
                    ->visible(fn (Pharmacy $record): bool => ! is_null($record->reward_handed_over_at))
                    ->requiresConfirmation()
                    ->action(function (Pharmacy $record): void {
                        $record->update([
                            'reward_handed_over_at' => null,
                            'reward_receiver_name' => null,
                            'reward_handover_notes' => null,
                        ]);

                        Notification::make()
                            ->title('Status Dibatalkan')
                            ->warning()
                            ->send();
                    }),
            ])
            ->filters([
                Filter::make('has_reward')
                    ->label('Hanya Apotek Berbelanja')
                    ->query(fn ($query) => $query->whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))),

                Filter::make('not_handed')
                    ->label('Belum Diserahkan')
                    ->query(fn ($query) => $query->whereNull('reward_handed_over_at')),

                Filter::make('handed')
                    ->label('Sudah Diserahkan')
                    ->query(fn ($query) => $query->whereNotNull('reward_handed_over_at')),

                SelectFilter::make('city')
                    ->label('Kota / Wilayah')
                    ->options(fn () => Pharmacy::distinct()->whereNotNull('city')->pluck('city', 'city')->toArray()),
            ])
            ->defaultPaginationPageOption(25);
    }

    public function exportCsv(): StreamedResponse
    {
        $rewardService = app(RewardCalculationService::class);
        $summaries = $rewardService->getAllPharmaciesRewardsSummary();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Hadiah_SMS_Gathering_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->streamDownload(function () use ($summaries) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Kode Apotek',
                'Nama Apotek / Klinik',
                'Kota',
                'Total Belanja (Rp)',
                'Jumlah Order (PO)',
                'Hadiah Langsung',
                'Hadiah Pilihan Terpilih',
                'Breakdown Voucher',
                'Total Voucher (Rp)',
                'Status Serah Terima',
                'Waktu Serah Terima',
                'Nama Penerima',
                'Catatan',
            ]);

            $no = 1;
            foreach ($summaries as $item) {
                $pharmacy = $item['pharmacy'];
                $rewards = $item['rewards'];

                $physicalList = [];
                foreach (($rewards['physical_rewards'] ?? []) as $giftName => $qty) {
                    $physicalList[] = is_numeric($giftName) ? (string) $qty : "{$giftName}: {$qty} pcs";
                }
                $physicalStr = implode('; ', $physicalList);

                $choicesList = [];
                foreach (($rewards['choice_selections'] ?? []) as $cs) {
                    $choicesList[] = ($cs['manufacturer_slug'] ?? '').': '.($cs['selected_option'] ?? '');
                }
                $choiceStr = implode('; ', $choicesList);

                $voucherList = [];
                foreach (($rewards['voucher_breakdown'] ?? []) as $slug => $nom) {
                    $voucherList[] = strtoupper($slug).': Rp '.number_format($nom, 0, ',', '.');
                }
                $voucherBreakdownStr = implode('; ', $voucherList);

                $statusStr = $item['is_handed_over'] ? 'Sudah Diserahkan' : 'Belum Diserahkan';
                $handedAtStr = $item['handed_over_at'] ? $item['handed_over_at']->format('d/m/Y H:i') : '-';

                fputcsv($handle, [
                    $no++,
                    $pharmacy->customer_code,
                    $pharmacy->name,
                    $pharmacy->city ?? '-',
                    number_format($item['total_spending'], 0, ',', '.'),
                    $item['order_count'],
                    $physicalStr ?: '-',
                    $choiceStr ?: '-',
                    $voucherBreakdownStr ?: '-',
                    number_format($rewards['vouchers'] ?? 0, 0, ',', '.'),
                    $statusStr,
                    $handedAtStr,
                    $item['receiver_name'] ?: '-',
                    $item['handover_notes'] ?: '-',
                ]);
            }

            fclose($handle);
        }, 'Rekap_Hadiah_SMS_Gathering.csv', $headers);
    }
}
