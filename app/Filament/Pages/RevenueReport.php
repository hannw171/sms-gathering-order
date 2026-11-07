<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RevenueStatsWidget;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ReportExportService;
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
                ->label('Download Excel (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportCsv'),
            Action::make('print')
                ->label('Cetak Laporan A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (): string => route('admin.reports.revenue.print'), shouldOpenInNewTab: true),
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
        return app(ReportExportService::class)->exportRevenue();
    }
}
