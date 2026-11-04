<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopProductsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('🔥 Top 5 Produk Terlaris')
            ->description('Produk farmasi dengan perolehan penjualan tertinggi')
            ->headerActions([
                Action::make('view_all')
                    ->label('Lihat Semua Produk (92 SKU) →')
                    ->url('/admin/product-sales-report')
                    ->color('primary')
                    ->button()
                    ->size('xs'),
            ])
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectSub(
                        OrderItem::whereColumn('order_items.product_id', 'products.id')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('orders.status', '!=', 'cancelled')
                            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)'),
                        'total_qty_sold'
                    )
                    ->selectSub(
                        OrderItem::whereColumn('order_items.product_id', 'products.id')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('orders.status', '!=', 'cancelled')
                            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0)'),
                        'total_sales_amount'
                    )
                    ->orderByDesc('total_qty_sold')
                    ->orderByDesc('total_sales_amount')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter()
                    ->color('gray'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray')
                    ->fontFamily(FontFamily::Mono),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Product $record): string => $record->manufacturer?->name ?? '-'),

                TextColumn::make('total_qty_sold')
                    ->label('Qty Terjual')
                    ->formatStateUsing(fn ($state, Product $record) => number_format((int) $state, 0, ',', '.').' '.$record->unit)
                    ->alignEnd()
                    ->weight(FontWeight::Bold)
                    ->color(fn ($state) => $state > 0 ? 'primary' : 'gray'),

                TextColumn::make('total_sales_amount')
                    ->label('Total Omset')
                    ->money('IDR', locale: 'id')
                    ->alignEnd()
                    ->weight(FontWeight::Black)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
            ])
            ->emptyStateHeading('Belum Ada Produk Terjual')
            ->emptyStateDescription('Data perolehan produk terlaris akan tampil saat pesanan masuk.')
            ->paginated(false);
    }
}
