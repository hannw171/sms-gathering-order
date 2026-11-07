<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductSalesStatsWidget;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReportExportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class ProductSalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static UnitEnum|string|null $navigationGroup = 'Laporan Gathering';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected string $view = 'filament.pages.product-sales-report';

    public function getTitle(): string|Htmlable
    {
        return 'Rekapitulasi Produk Terjual';
    }

    public static function getNavigationLabel(): string
    {
        return 'Produk Terjual';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductSalesStatsWidget::class,
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
                ->url(fn (): string => route('admin.reports.product-sales.print'), shouldOpenInNewTab: true),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectSub(
                        OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->whereColumn('order_items.product_id', 'products.id')
                            ->where('orders.status', '!=', 'cancelled')
                            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)'),
                        'qty_sold'
                    )
                    ->selectSub(
                        OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->whereColumn('order_items.product_id', 'products.id')
                            ->where('orders.status', '!=', 'cancelled')
                            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0)'),
                        'total_revenue'
                    )
                    ->with('manufacturer')
            )
            ->defaultSort('qty_sold', 'desc')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->fontFamily(FontFamily::Mono)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('manufacturer.name')
                    ->label('Pabrikan')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('uom')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('qty_sold')
                    ->label('Qty Terjual')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_revenue')
                    ->label('Total Omset')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold),
            ])
            ->filters([
                SelectFilter::make('manufacturer_id')
                    ->label('Pabrikan')
                    ->relationship('manufacturer', 'name'),

                TernaryFilter::make('sales_status')
                    ->label('Status Penjualan')
                    ->placeholder('Semua Produk')
                    ->trueLabel('Terjual Saja')
                    ->falseLabel('Belum Terjual')
                    ->queries(
                        true: fn ($q) => $q->whereHas('orderItems.order', fn ($oq) => $oq->where('status', '!=', 'cancelled')),
                        false: fn ($q) => $q->whereDoesntHave('orderItems.order', fn ($oq) => $oq->where('status', '!=', 'cancelled')),
                    ),
            ])
            ->defaultPaginationPageOption(25);
    }

    public function exportCsv(): StreamedResponse
    {
        return app(ReportExportService::class)->exportProductSales();
    }
}
