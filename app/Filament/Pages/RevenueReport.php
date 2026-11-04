<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RevenueStatsWidget;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class RevenueReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static UnitEnum|string|null $navigationGroup = 'Laporan Gathering';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected string $view = 'filament.pages.revenue-report';

    public function getTitle(): string|Htmlable
    {
        return 'Rekapitulasi Omset & GMV Pabrikan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Omset Pabrikan';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RevenueStatsWidget::class,
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
                ->label('Cetak Laporan A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes(['onclick' => 'window.print()']),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Manufacturer::query()->withCount('products'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pabrikan')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('products_count')
                    ->label('Total SKU')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('sold_sku')
                    ->label('SKU Terjual')
                    ->state(function (Manufacturer $record): int {
                        return OrderItem::whereHas('product', fn ($q) => $q->where('manufacturer_id', $record->id))
                            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                            ->distinct('product_id')
                            ->count('product_id');
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_units')
                    ->label('Volume Terjual (Box/Fls)')
                    ->state(function (Manufacturer $record): int {
                        return (int) OrderItem::whereHas('product', fn ($q) => $q->where('manufacturer_id', $record->id))
                            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                            ->sum('quantity');
                    })
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('total_revenue')
                    ->label('Total Omset')
                    ->money('IDR', locale: 'id')
                    ->state(function (Manufacturer $record): float {
                        return (float) OrderItem::whereHas('product', fn ($q) => $q->where('manufacturer_id', $record->id))
                            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                            ->sum('subtotal');
                    })
                    ->alignEnd()
                    ->weight(FontWeight::Bold),

                TextColumn::make('contribution')
                    ->label('Kontribusi Omset (%)')
                    ->state(function (Manufacturer $record): string {
                        $totalGMV = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
                        if ($totalGMV <= 0) {
                            return '0.0%';
                        }
                        $rev = (float) OrderItem::whereHas('product', fn ($q) => $q->where('manufacturer_id', $record->id))
                            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                            ->sum('subtotal');

                        return number_format(($rev / $totalGMV) * 100, 1, ',', '.').'%';
                    })
                    ->badge()
                    ->color('success')
                    ->alignEnd(),
            ])
            ->paginated(false);
    }

    public function exportCsv(): StreamedResponse
    {
        $mAggregates = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('products.manufacturer_id, COUNT(DISTINCT products.id) as sold_skus, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.manufacturer_id')
            ->get()
            ->keyBy('manufacturer_id');

        $totalGMV = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $manufacturers = Manufacturer::withCount(['products' => fn ($q) => $q->where('is_active', true)])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Omset_Pabrikan_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->streamDownload(function () use ($manufacturers, $mAggregates, $totalGMV) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Nama Pabrikan',
                'Jumlah SKU Aktif',
                'SKU Terjual',
                'Total Volume Terjual (Box/Fls)',
                'Total Omset Penjualan (Rp)',
                'Kontribusi Omset (%)',
            ]);

            $no = 1;
            foreach ($manufacturers as $m) {
                $agg = $mAggregates->get($m->id);
                $soldSkus = $agg ? (int) $agg->sold_skus : 0;
                $qty = $agg ? (int) $agg->total_qty : 0;
                $rev = $agg ? (float) $agg->total_revenue : 0.0;
                $pct = $totalGMV > 0 ? (($rev / $totalGMV) * 100) : 0.0;

                fputcsv($handle, [
                    $no++,
                    $m->name,
                    $m->products_count,
                    $soldSkus,
                    $qty,
                    number_format($rev, 0, ',', '.'),
                    number_format($pct, 2, ',', '.').'%',
                ]);
            }

            fclose($handle);
        }, 'Rekap_Omset_Pabrikan.csv', $headers);
    }
}
