<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductSalesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalSku = Product::where('is_active', true)->count();

        $salesAgg = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('COUNT(DISTINCT order_items.product_id) as sold_skus, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->first();

        $soldSkus = $salesAgg ? (int) $salesAgg->sold_skus : 0;
        $totalQty = $salesAgg ? (int) $salesAgg->total_qty : 0;
        $totalRevenue = $salesAgg ? (float) $salesAgg->total_revenue : 0.0;

        return [
            Stat::make('Katalog Produk Aktif', (string) $totalSku.' SKU')
                ->description('Dari 9 Pabrikan Farmasi')
                ->icon('heroicon-o-cube'),

            Stat::make('SKU Berhasil Terjual', (string) $soldSkus.' SKU')
                ->description('Terdapat pesanan masuk')
                ->color('info')
                ->icon('heroicon-o-check-badge'),

            Stat::make('Total Volume Terjual', number_format($totalQty, 0, ',', '.').' Box/Fls')
                ->description('Akumulasi seluruh produk')
                ->color('warning')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Total Omset Produk', 'Rp '.number_format($totalRevenue, 0, ',', '.'))
                ->description('Penjualan seluruh pabrik')
                ->color('success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
