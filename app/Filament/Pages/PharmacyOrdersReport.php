<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PharmacyOrdersStatsWidget;
use App\Models\Manufacturer;
use App\Models\Pharmacy;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class PharmacyOrdersReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static UnitEnum|string|null $navigationGroup = 'Laporan Gathering';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected string $view = 'filament.pages.pharmacy-orders-report';

    public function getTitle(): string|Htmlable
    {
        return 'Rekap Belanja Detail Per Apotek';
    }

    public static function getNavigationLabel(): string
    {
        return 'Belanja Per Apotek';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PharmacyOrdersStatsWidget::class,
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
                ->label('Cetak Rekap A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes(['onclick' => 'window.print()']),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Rekapitulasi apotek belanja dari pabrikan apa saja beserta breakdown item produk dan nominal transaksinya.')
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

                TextColumn::make('manufacturers_bought')
                    ->label('Pabrikan yang Dibelanjakan')
                    ->state(function (Pharmacy $record): string {
                        $orders = $record->orders->where('status', '!=', 'cancelled');
                        $mNames = $orders->flatMap->items
                            ->map(fn ($item) => $item->manufacturer_name ?? $item->product?->manufacturer?->name)
                            ->filter()
                            ->unique()
                            ->values();

                        return $mNames->isEmpty() ? '-' : $mNames->implode(', ');
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state !== '-' ? 'info' : 'gray')
                    ->wrap(),

                TextColumn::make('total_items')
                    ->label('Total Volume')
                    ->state(fn (Pharmacy $record): int => (int) $record->orders->where('status', '!=', 'cancelled')->sum('total_items'))
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('total_spending')
                    ->label('Total Belanja')
                    ->money('IDR', locale: 'id')
                    ->state(fn (Pharmacy $record): float => (float) $record->orders->where('status', '!=', 'cancelled')->sum('total_amount'))
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Black)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('orders_count')
                    ->label('Jumlah PO')
                    ->state(fn (Pharmacy $record): int => $record->orders->where('status', '!=', 'cancelled')->count())
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('Rincian Belanja')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->button()
                    ->size('xs')
                    ->slideOver()
                    ->modalHeading(fn (Pharmacy $record): string => 'Rincian Belanja: '.$record->name)
                    ->modalDescription(fn (Pharmacy $record): string => $record->customer_code.' • '.($record->city ?? '-'))
                    ->modalContent(fn (Pharmacy $record) => view('filament.pages.partials.pharmacy-orders-detail', ['pharmacy' => $record])),
            ])
            ->filters([
                Filter::make('has_orders')
                    ->label('Sudah Belanja Saja')
                    ->query(fn ($query) => $query->whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))),

                Filter::make('no_orders')
                    ->label('Belum Belanja')
                    ->query(fn ($query) => $query->whereDoesntHave('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))),

                Filter::make('manufacturer')
                    ->label('Filter Berdasarkan Pabrikan')
                    ->form([
                        Select::make('manufacturer_id')
                            ->label('Pilih Pabrikan')
                            ->options(fn () => Manufacturer::pluck('name', 'id')->toArray())
                            ->placeholder('Semua Pabrikan'),
                    ])
                    ->query(function ($query, array $data) {
                        if (! empty($data['manufacturer_id'])) {
                            $mId = $data['manufacturer_id'];
                            $query->whereHas('orders', function ($oq) use ($mId) {
                                $oq->where('status', '!=', 'cancelled')
                                    ->whereHas('items.product', fn ($pq) => $pq->where('manufacturer_id', $mId));
                            });
                        }
                    }),

                SelectFilter::make('city')
                    ->label('Kota / Wilayah')
                    ->options(fn () => Pharmacy::distinct()->whereNotNull('city')->pluck('city', 'city')->toArray()),
            ])
            ->defaultPaginationPageOption(25);
    }

    public function exportCsv(): StreamedResponse
    {
        $pharmacies = Pharmacy::with([
            'orders' => function ($q) {
                $q->where('status', '!=', 'cancelled')->with(['items.product.manufacturer']);
            },
        ])->orderBy('customer_code')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Belanja_Seluruh_Apotek_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->streamDownload(function () use ($pharmacies) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Apotek',
                'Nama Apotek',
                'Kota',
                'No. PO',
                'Waktu Order',
                'Pabrikan',
                'SKU',
                'Nama Produk',
                'Satuan',
                'Harga Satuan (Rp)',
                'Qty',
                'Subtotal (Rp)',
            ]);

            foreach ($pharmacies as $pharmacy) {
                foreach ($pharmacy->orders as $order) {
                    foreach ($order->items as $item) {
                        fputcsv($handle, [
                            $pharmacy->customer_code,
                            $pharmacy->name,
                            $pharmacy->city ?? '-',
                            $order->order_number,
                            $order->created_at->format('d/m/Y H:i:s'),
                            $item->manufacturer_name ?? ($item->product?->manufacturer?->name ?? 'Lainnya'),
                            $item->product?->sku ?? '-',
                            $item->product_name,
                            $item->product?->unit ?? 'box',
                            number_format($item->price, 0, ',', '.'),
                            $item->quantity,
                            number_format($item->subtotal, 0, ',', '.'),
                        ]);
                    }
                }
            }

            fclose($handle);
        }, 'Rekap_Belanja_Seluruh_Apotek.csv', $headers);
    }
}
