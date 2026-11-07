<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Services\GatheringService;
use App\Services\ReportExportService;
use App\Services\RewardCalculationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportPrintController extends Controller
{
    /**
     * 1. Print A4: Rekap Penerima Hadiah & Voucher
     */
    public function rewardRecap(RewardCalculationService $rewardService): View
    {
        $event = EventSetting::current();
        $summaries = $rewardService->getAllPharmaciesRewardsSummary();

        // Filter to pharmacies that have orders or rewards, sorted by spending desc
        $activeSummaries = collect($summaries)
            ->filter(fn (array $item) => $item['total_spending'] > 0 || $item['has_any_reward'])
            ->sortByDesc('total_spending')
            ->values();

        $totalSpending = (float) $activeSummaries->sum('total_spending');
        $totalVouchers = (float) $activeSummaries->sum(fn ($i) => (float) ($i['rewards']['vouchers'] ?? 0));
        $rewardedCount = $activeSummaries->where('has_any_reward', true)->count();
        $handedOverCount = $activeSummaries->where('is_handed_over', true)->count();

        // Aggregate physical + choice items across all pharmacies for warehouse preparation table
        $itemRecap = [];
        foreach ($activeSummaries as $item) {
            $rewards = $item['rewards'];
            foreach (($rewards['physical_rewards'] ?? []) as $giftName => $qty) {
                $label = is_numeric($giftName) ? (string) $qty : (string) $giftName;
                $count = is_numeric($giftName) ? 1 : (int) $qty;
                $key = 'FISIK|'.$label;
                if (! isset($itemRecap[$key])) {
                    $itemRecap[$key] = ['type' => 'Hadiah Langsung (Fisik)', 'name' => $label, 'qty' => 0];
                }
                $itemRecap[$key]['qty'] += $count;
            }
            foreach (($rewards['choice_selections'] ?? []) as $cs) {
                $opt = $cs['selected_option'] ?? '';
                $mSlug = strtoupper($cs['manufacturer_slug'] ?? '');
                if ($opt) {
                    $label = $mSlug ? "{$opt} ({$mSlug})" : $opt;
                    $key = 'PILIHAN|'.$label;
                    if (! isset($itemRecap[$key])) {
                        $itemRecap[$key] = ['type' => 'Hadiah Pilihan Apotek', 'name' => $label, 'qty' => 0];
                    }
                    $itemRecap[$key]['qty'] += 1;
                }
            }
        }

        return view('reports.reward-recap', [
            'event' => $event,
            'summaries' => $activeSummaries,
            'totalSpending' => $totalSpending,
            'totalVouchers' => $totalVouchers,
            'rewardedCount' => $rewardedCount,
            'handedOverCount' => $handedOverCount,
            'itemRecap' => array_values($itemRecap),
        ]);
    }

    /**
     * 2. Print A4: Rekapitulasi Produk Terjual
     */
    public function productSales(): View
    {
        $event = EventSetting::current();

        $salesAgg = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue, COUNT(DISTINCT orders.pharmacy_id) as pharmacy_count')
            ->groupBy('order_items.product_id')
            ->get()
            ->keyBy('product_id');

        $products = Product::with('manufacturer')
            ->get()
            ->map(function (Product $prod) use ($salesAgg) {
                $agg = $salesAgg->get($prod->id);
                $prod->qty_sold = $agg ? (int) $agg->total_qty : 0;
                $prod->total_revenue = $agg ? (float) $agg->total_revenue : 0.0;
                $prod->pharmacy_count = $agg ? (int) $agg->pharmacy_count : 0;

                return $prod;
            })
            ->sortByDesc('qty_sold')
            ->values();

        $soldProducts = $products->where('qty_sold', '>', 0)->values();
        $unsoldProducts = $products->where('qty_sold', '<=', 0)->values();

        return view('reports.product-sales', [
            'event' => $event,
            'products' => $products,
            'soldProducts' => $soldProducts,
            'unsoldProducts' => $unsoldProducts,
            'totalSkus' => $products->count(),
            'soldSkuCount' => $soldProducts->count(),
            'totalVolume' => (int) $products->sum('qty_sold'),
            'totalRevenue' => (float) $products->sum('total_revenue'),
        ]);
    }

    /**
     * 3. Print A4: Rekapitulasi Omset & GMV Pabrikan
     */
    public function revenue(): View
    {
        $event = EventSetting::current();

        $mAggregates = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('products.manufacturer_id, COUNT(DISTINCT products.id) as sold_skus, COUNT(DISTINCT orders.pharmacy_id) as pharmacy_count, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.manufacturer_id')
            ->get()
            ->keyBy('manufacturer_id');

        $totalGMV = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');

        $manufacturers = Manufacturer::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(function (Manufacturer $m) use ($mAggregates, $totalGMV) {
                $agg = $mAggregates->get($m->id);
                $m->sold_skus = $agg ? (int) $agg->sold_skus : 0;
                $m->pharmacy_count = $agg ? (int) $agg->pharmacy_count : 0;
                $m->total_qty = $agg ? (int) $agg->total_qty : 0;
                $m->total_revenue = $agg ? (float) $agg->total_revenue : 0.0;
                $m->contribution = $totalGMV > 0 ? (($m->total_revenue / $totalGMV) * 100) : 0.0;

                return $m;
            })
            ->sortByDesc('total_revenue')
            ->values();

        return view('reports.revenue', [
            'event' => $event,
            'manufacturers' => $manufacturers,
            'totalGMV' => $totalGMV,
            'totalVolume' => (int) $manufacturers->sum('total_qty'),
            'totalSkus' => (int) $manufacturers->sum('products_count'),
            'totalSoldSkus' => (int) $manufacturers->sum('sold_skus'),
        ]);
    }

    /**
     * 4. Print A4: Rekapitulasi Peringkat Transaksi Apotek (Leaderboard)
     */
    public function leaderboard(GatheringService $gatheringService): View
    {
        $event = EventSetting::current();
        $leaderboard = $gatheringService->getLeaderboard();
        $graha = Manufacturer::where('slug', 'graha-farma')->first();
        $grahaStandings = $graha
            ? $gatheringService->getLeaderboard($graha->id)->where('total_spending', '>', 0)->take(10)->values()
            : collect();

        $activePharmacies = $leaderboard->where('total_spending', '>', 0)->values();
        $checkedInCount = $leaderboard->where('is_checked_in', true)->count();
        $totalSpending = (float) $leaderboard->sum('total_spending');
        $totalOrders = (int) $leaderboard->sum('orders_count');

        return view('reports.leaderboard', [
            'event' => $event,
            'leaderboard' => $leaderboard,
            'activePharmacies' => $activePharmacies,
            'grahaStandings' => $grahaStandings,
            'checkedInCount' => $checkedInCount,
            'totalSpending' => $totalSpending,
            'totalOrders' => $totalOrders,
        ]);
    }

    /**
     * 5. Print A4: Rekap Belanja Detail Per Apotek (Gaya Laporan Inkaso sfa_phar)
     */
    public function pharmacyOrders(): View
    {
        $event = EventSetting::current();

        $pharmacies = Pharmacy::query()
            ->select('pharmacies.*')
            ->selectSub(
                Order::whereColumn('orders.pharmacy_id', 'pharmacies.id')
                    ->where('status', '!=', 'cancelled')
                    ->selectRaw('COALESCE(SUM(total_amount), 0)'),
                'total_spending'
            )
            ->with([
                'orders' => fn ($q) => $q->where('status', '!=', 'cancelled')->with(['items.product.manufacturer']),
            ])
            ->orderByDesc('total_spending')
            ->orderBy('name')
            ->get();

        $activePharmacies = $pharmacies->filter(fn (Pharmacy $p) => $p->orders->isNotEmpty())->values();
        $grandTotal = (float) $activePharmacies->sum('total_spending');
        $totalPoCount = (int) $activePharmacies->sum(fn (Pharmacy $p) => $p->orders->count());
        $totalItemQty = (int) $activePharmacies->sum(fn (Pharmacy $p) => $p->orders->sum('total_items'));

        return view('reports.pharmacy-orders', [
            'event' => $event,
            'pharmacies' => $activePharmacies,
            'allPharmaciesCount' => $pharmacies->count(),
            'grandTotal' => $grandTotal,
            'totalPoCount' => $totalPoCount,
            'totalItemQty' => $totalItemQty,
        ]);
    }

    /**
     * 6. Print A4: Rincian Belanja & Berita Acara Serah Terima per 1 Apotek
     */
    public function pharmacyDetail(Pharmacy $pharmacy, RewardCalculationService $rewardService): View
    {
        $event = EventSetting::current();
        $orders = $pharmacy->orders()->where('status', '!=', 'cancelled')->with('items.product.manufacturer')->get();
        $totalSpending = (float) $orders->sum('total_amount');
        $totalQty = (int) $orders->sum('total_items');
        $rewards = $rewardService->calculateForPharmacy($pharmacy);

        // Group items by manufacturer
        $itemsByManufacturer = $orders->flatMap->items
            ->groupBy(fn ($item) => $item->manufacturer_name ?? ($item->product?->manufacturer?->name ?? 'Pabrikan Lainnya'))
            ->map(function ($items, $mName) use ($rewards) {
                $first = $items->first();
                $mSlug = $first->product?->manufacturer?->slug ?? Str::slug($mName);
                $mVoucher = (float) ($rewards['vouchers_by_manufacturer'][$mSlug] ?? ($rewards['details'][$mSlug]['vouchers'] ?? 0));

                $aggregated = $items->groupBy('product_name')->map(function ($prodItems) {
                    $firstItem = $prodItems->first();

                    return [
                        'sku' => $firstItem->product?->sku ?? '-',
                        'name' => $firstItem->product_name,
                        'uom' => strtoupper($firstItem->uom ?? $firstItem->product?->uom ?? 'BOX'),
                        'price' => (float) $firstItem->price,
                        'total_qty' => (int) $prodItems->sum('quantity'),
                        'total_subtotal' => (float) $prodItems->sum('subtotal'),
                    ];
                })->values();

                return [
                    'manufacturer_name' => $mName,
                    'manufacturer_slug' => $mSlug,
                    'voucher' => $mVoucher,
                    'items' => $aggregated,
                    'subtotal' => (float) $items->sum('subtotal'),
                    'total_qty' => (int) $items->sum('quantity'),
                ];
            })->values();

        return view('reports.pharmacy-handover', [
            'event' => $event,
            'pharmacy' => $pharmacy,
            'orders' => $orders,
            'totalSpending' => $totalSpending,
            'totalQty' => $totalQty,
            'rewards' => $rewards,
            'itemsByManufacturer' => $itemsByManufacturer,
        ]);
    }

    /**
     * 7. Export Excel (.xlsx) untuk 1 Apotek
     */
    public function pharmacyExcel(Pharmacy $pharmacy, ReportExportService $exportService): StreamedResponse
    {
        return $exportService->exportSinglePharmacy($pharmacy);
    }
}
