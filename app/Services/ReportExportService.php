<?php

namespace App\Services;

use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Create shared styles for Excel tables.
     *
     * @return array<string, Style>
     */
    protected function createStyles(): array
    {
        $thinBorder = new Border(
            new BorderPart(Border::TOP, 'CBD5E1', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, 'CBD5E1', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, 'CBD5E1', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, 'CBD5E1', Border::WIDTH_THIN, Border::STYLE_SOLID),
        );

        $headerBorder = new Border(
            new BorderPart(Border::TOP, '065F46', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, '065F46', Border::WIDTH_MEDIUM, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, '065F46', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, '065F46', Border::WIDTH_THIN, Border::STYLE_SOLID),
        );

        $totalBorder = new Border(
            new BorderPart(Border::TOP, '64748B', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, '64748B', Border::WIDTH_MEDIUM, Border::STYLE_DOUBLE),
            new BorderPart(Border::LEFT, 'CBD5E1', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, 'CBD5E1', Border::WIDTH_THIN, Border::STYLE_SOLID),
        );

        return [
            'company' => (new Style)
                ->setFontBold()
                ->setFontSize(14)
                ->setFontColor('0D47A1'),

            'title' => (new Style)
                ->setFontBold()
                ->setFontSize(12)
                ->setFontColor('111827'),

            'meta' => (new Style)
                ->setFontSize(10)
                ->setFontColor('475569'),

            'section' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('065F46')
                ->setBackgroundColor('ECFDF5'),

            'thLeft' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('047857')
                ->setCellAlignment(CellAlignment::LEFT)
                ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
                ->setBorder($headerBorder),

            'thCenter' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('047857')
                ->setCellAlignment(CellAlignment::CENTER)
                ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
                ->setBorder($headerBorder),

            'thRight' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('047857')
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
                ->setBorder($headerBorder),

            'tdLeft' => (new Style)
                ->setFontSize(10)
                ->setCellAlignment(CellAlignment::LEFT)
                ->setCellVerticalAlignment(CellVerticalAlignment::TOP)
                ->setShouldWrapText(true)
                ->setBorder($thinBorder),

            'tdBoldLeft' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setCellAlignment(CellAlignment::LEFT)
                ->setCellVerticalAlignment(CellVerticalAlignment::TOP)
                ->setShouldWrapText(true)
                ->setBorder($thinBorder),

            'tdCenter' => (new Style)
                ->setFontSize(10)
                ->setCellAlignment(CellAlignment::CENTER)
                ->setCellVerticalAlignment(CellVerticalAlignment::TOP)
                ->setBorder($thinBorder),

            'tdBoldCenter' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setCellAlignment(CellAlignment::CENTER)
                ->setCellVerticalAlignment(CellVerticalAlignment::TOP)
                ->setBorder($thinBorder),

            'tdNumber' => (new Style)
                ->setFontSize(10)
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setCellVerticalAlignment(CellVerticalAlignment::TOP)
                ->setFormat('#,##0')
                ->setBorder($thinBorder),

            'tdCurrency' => (new Style)
                ->setFontSize(10)
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setCellVerticalAlignment(CellVerticalAlignment::TOP)
                ->setFormat('#,##0')
                ->setBorder($thinBorder),

            'tdBoldCurrency' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setCellVerticalAlignment(CellVerticalAlignment::TOP)
                ->setFormat('#,##0')
                ->setBorder($thinBorder),

            'subLeft' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('065F46')
                ->setBackgroundColor('F0FDF4')
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBorder($thinBorder),

            'subRight' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('065F46')
                ->setBackgroundColor('F0FDF4')
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setFormat('#,##0')
                ->setBorder($thinBorder),

            'totalLeft' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('1E3A8A')
                ->setBackgroundColor('EFF6FF')
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBorder($totalBorder),

            'totalCenter' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('1E3A8A')
                ->setBackgroundColor('EFF6FF')
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBorder($totalBorder),

            'totalNumber' => (new Style)
                ->setFontBold()
                ->setFontSize(10)
                ->setFontColor('1E3A8A')
                ->setBackgroundColor('EFF6FF')
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setFormat('#,##0')
                ->setBorder($totalBorder),
        ];
    }

    /**
     * Helper to write standard corporate report header block.
     */
    protected function writeReportHeader(Writer $writer, array $styles, string $reportTitle, string $summaryLine): void
    {
        $event = EventSetting::current();
        $nowStr = now()->translatedFormat('l, d F Y - H:i').' WIB';

        $writer->addRow(Row::fromValues(['PT. SATRIA MEDIKA SEJAHTERA'], $styles['company']));
        $writer->addRow(Row::fromValues([strtoupper($reportTitle)], $styles['title']));
        $writer->addRow(Row::fromValues(["Acara: {$event->event_name}  |  Tanggal Cetak: {$nowStr}"], $styles['meta']));
        $writer->addRow(Row::fromValues([$summaryLine], $styles['section']));
        $writer->addRow(Row::fromValues([]));
    }

    /**
     * Stream an OpenSpout writer as an XLSX download response.
     */
    protected function streamXlsx(string $filename, callable $callback): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->streamDownload(function () use ($callback) {
            $options = new Options;
            $writer = new Writer($options);
            $writer->openToFile('php://output');

            $callback($writer, $options);

            $writer->close();
        }, $filename, $headers);
    }

    /**
     * 1. Export Reward Recap Report (.xlsx)
     */
    public function exportRewardRecap(): StreamedResponse
    {
        $rewardService = app(RewardCalculationService::class);
        $summaries = $rewardService->getAllPharmaciesRewardsSummary();
        $filename = 'Rekap_Hadiah_SMS_Gathering_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamXlsx($filename, function (Writer $writer, Options $options) use ($summaries) {
            $styles = $this->createStyles();

            $widths = [1 => 6, 2 => 14, 3 => 30, 4 => 16, 5 => 18, 6 => 10, 7 => 34, 8 => 34, 9 => 32, 10 => 18, 11 => 18, 12 => 18, 13 => 22, 14 => 24];
            foreach ($widths as $col => $w) {
                $options->setColumnWidth($w, $col);
            }

            $totalSpendingAll = collect($summaries)->sum('total_spending');
            $totalVoucherAll = collect($summaries)->sum(fn ($i) => (float) ($i['rewards']['vouchers'] ?? 0));
            $summaryText = 'Total Apotek: '.count($summaries).'  |  Total Belanja: Rp '.number_format($totalSpendingAll, 0, ',', '.').'  |  Total Voucher: Rp '.number_format($totalVoucherAll, 0, ',', '.');

            $this->writeReportHeader($writer, $styles, 'LAPORAN REKAPITULASI PENERIMA HADIAH & VOUCHER', $summaryText);

            $writer->addRow(Row::fromValuesWithStyles([
                'No',
                'Kode Apotek',
                'Nama Apotek / Klinik',
                'Kota',
                'Total Belanja (Rp)',
                'Jml PO',
                'Hadiah Langsung (Fisik)',
                'Hadiah Pilihan Terpilih',
                'Breakdown Voucher Pabrikan',
                'Total Voucher (Rp)',
                'Status Serah Terima',
                'Waktu Serah Terima',
                'Nama Penerima',
                'Catatan',
            ], null, [
                0 => $styles['thCenter'],
                1 => $styles['thCenter'],
                2 => $styles['thLeft'],
                3 => $styles['thLeft'],
                4 => $styles['thRight'],
                5 => $styles['thCenter'],
                6 => $styles['thLeft'],
                7 => $styles['thLeft'],
                8 => $styles['thLeft'],
                9 => $styles['thRight'],
                10 => $styles['thCenter'],
                11 => $styles['thCenter'],
                12 => $styles['thLeft'],
                13 => $styles['thLeft'],
            ]));

            $no = 1;
            $grandOrders = 0;

            foreach ($summaries as $item) {
                $pharmacy = $item['pharmacy'];
                $rewards = $item['rewards'];
                $grandOrders += (int) $item['order_count'];

                $physicalList = [];
                foreach (($rewards['physical_rewards'] ?? []) as $giftName => $qty) {
                    $physicalList[] = is_numeric($giftName) ? (string) $qty : "{$giftName}: {$qty} pcs";
                }
                $physicalStr = implode("\n", $physicalList) ?: '-';

                $choicesList = [];
                foreach (($rewards['choice_selections'] ?? []) as $cs) {
                    $choicesList[] = strtoupper($cs['manufacturer_slug'] ?? '').': '.($cs['selected_option'] ?? '');
                }
                $choiceStr = implode("\n", $choicesList) ?: '-';

                $voucherList = [];
                foreach (($rewards['voucher_breakdown'] ?? []) as $slug => $nom) {
                    if ($nom > 0) {
                        $voucherList[] = strtoupper($slug).': Rp '.number_format($nom, 0, ',', '.');
                    }
                }
                $voucherBreakdownStr = implode("\n", $voucherList) ?: '-';

                $statusStr = $item['is_handed_over'] ? 'Sudah Diserahkan' : 'Belum Diserahkan';
                $handedAtStr = $item['handed_over_at'] ? $item['handed_over_at']->format('d/m/Y H:i') : '-';

                $writer->addRow(Row::fromValuesWithStyles([
                    $no++,
                    $pharmacy->customer_code,
                    $pharmacy->name,
                    $pharmacy->city ?? '-',
                    (float) $item['total_spending'],
                    (int) $item['order_count'],
                    $physicalStr,
                    $choiceStr,
                    $voucherBreakdownStr,
                    (float) ($rewards['vouchers'] ?? 0),
                    $statusStr,
                    $handedAtStr,
                    $item['receiver_name'] ?: '-',
                    $item['handover_notes'] ?: '-',
                ], null, [
                    0 => $styles['tdCenter'],
                    1 => $styles['tdCenter'],
                    2 => $styles['tdBoldLeft'],
                    3 => $styles['tdLeft'],
                    4 => $styles['tdCurrency'],
                    5 => $styles['tdCenter'],
                    6 => $styles['tdLeft'],
                    7 => $styles['tdLeft'],
                    8 => $styles['tdLeft'],
                    9 => $styles['tdBoldCurrency'],
                    10 => $styles['tdCenter'],
                    11 => $styles['tdCenter'],
                    12 => $styles['tdLeft'],
                    13 => $styles['tdLeft'],
                ]));
            }

            $writer->addRow(Row::fromValuesWithStyles([
                '',
                '',
                'GRAND TOTAL',
                '',
                (float) $totalSpendingAll,
                (int) $grandOrders,
                '',
                '',
                '',
                (float) $totalVoucherAll,
                '',
                '',
                '',
                '',
            ], null, [
                0 => $styles['totalLeft'],
                1 => $styles['totalLeft'],
                2 => $styles['totalLeft'],
                3 => $styles['totalLeft'],
                4 => $styles['totalNumber'],
                5 => $styles['totalCenter'],
                6 => $styles['totalLeft'],
                7 => $styles['totalLeft'],
                8 => $styles['totalLeft'],
                9 => $styles['totalNumber'],
                10 => $styles['totalLeft'],
                11 => $styles['totalLeft'],
                12 => $styles['totalLeft'],
                13 => $styles['totalLeft'],
            ]));
        });
    }

    /**
     * 2. Export Product Sales Report (.xlsx)
     */
    public function exportProductSales(): StreamedResponse
    {
        $salesAgg = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('order_items.product_id')
            ->get()
            ->keyBy('product_id');

        $products = Product::with('manufacturer')->get()->sortByDesc(function (Product $p) use ($salesAgg) {
            return (int) ($salesAgg->get($p->id)->total_qty ?? 0);
        })->values();

        $filename = 'Rekap_Produk_Terjual_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamXlsx($filename, function (Writer $writer, Options $options) use ($products, $salesAgg) {
            $styles = $this->createStyles();

            $widths = [1 => 6, 2 => 14, 3 => 34, 4 => 28, 5 => 12, 6 => 18, 7 => 15, 8 => 20];
            foreach ($widths as $col => $w) {
                $options->setColumnWidth($w, $col);
            }

            $grandQty = (int) $salesAgg->sum('total_qty');
            $grandRev = (float) $salesAgg->sum('total_revenue');
            $soldSkuCount = $salesAgg->filter(fn ($a) => $a->total_qty > 0)->count();

            $summaryText = "Katalog Aktif: {$products->count()} SKU  |  SKU Terjual: {$soldSkuCount} SKU  |  Total Volume: ".number_format($grandQty, 0, ',', '.').' Box/Fls  |  Total Omset: Rp '.number_format($grandRev, 0, ',', '.');
            $this->writeReportHeader($writer, $styles, 'LAPORAN REKAPITULASI PRODUK TERJUAL', $summaryText);

            $writer->addRow(Row::fromValuesWithStyles([
                'No',
                'SKU',
                'Nama Produk',
                'Pabrikan',
                'Satuan',
                'Harga Satuan (Rp)',
                'Qty Terjual',
                'Total Omset (Rp)',
            ], null, [
                0 => $styles['thCenter'],
                1 => $styles['thCenter'],
                2 => $styles['thLeft'],
                3 => $styles['thLeft'],
                4 => $styles['thCenter'],
                5 => $styles['thRight'],
                6 => $styles['thRight'],
                7 => $styles['thRight'],
            ]));

            $no = 1;
            foreach ($products as $prod) {
                $agg = $salesAgg->get($prod->id);
                $qty = $agg ? (int) $agg->total_qty : 0;
                $rev = $agg ? (float) $agg->total_revenue : 0.0;

                $writer->addRow(Row::fromValuesWithStyles([
                    $no++,
                    $prod->sku,
                    $prod->name,
                    $prod->manufacturer?->name ?? '-',
                    strtoupper($prod->uom ?? $prod->unit ?? 'BOX'),
                    (float) $prod->price,
                    $qty,
                    $rev,
                ], null, [
                    0 => $styles['tdCenter'],
                    1 => $styles['tdCenter'],
                    2 => $styles['tdBoldLeft'],
                    3 => $styles['tdLeft'],
                    4 => $styles['tdCenter'],
                    5 => $styles['tdCurrency'],
                    6 => $styles['tdNumber'],
                    7 => $styles['tdBoldCurrency'],
                ]));
            }

            $writer->addRow(Row::fromValuesWithStyles([
                '',
                '',
                'GRAND TOTAL',
                '',
                '',
                '',
                $grandQty,
                $grandRev,
            ], null, [
                0 => $styles['totalLeft'],
                1 => $styles['totalLeft'],
                2 => $styles['totalLeft'],
                3 => $styles['totalLeft'],
                4 => $styles['totalLeft'],
                5 => $styles['totalLeft'],
                6 => $styles['totalNumber'],
                7 => $styles['totalNumber'],
            ]));
        });
    }

    /**
     * 3. Export Manufacturer Revenue Report (.xlsx)
     */
    public function exportRevenue(): StreamedResponse
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
        $manufacturers = Manufacturer::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->sortByDesc(fn ($m) => (float) ($mAggregates->get($m->id)->total_revenue ?? 0))
            ->values();

        $filename = 'Rekap_Omset_Pabrikan_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamXlsx($filename, function (Writer $writer, Options $options) use ($manufacturers, $mAggregates, $totalGMV) {
            $styles = $this->createStyles();

            $widths = [1 => 6, 2 => 32, 3 => 16, 4 => 16, 5 => 22, 6 => 22, 7 => 18];
            foreach ($widths as $col => $w) {
                $options->setColumnWidth($w, $col);
            }

            $grandQty = (int) $mAggregates->sum('total_qty');
            $summaryText = "Total Pabrikan: {$manufacturers->count()}  |  Total Volume: ".number_format($grandQty, 0, ',', '.').' Box/Fls  |  Total GMV: Rp '.number_format($totalGMV, 0, ',', '.');
            $this->writeReportHeader($writer, $styles, 'LAPORAN REKAPITULASI OMSET & GMV PABRIKAN', $summaryText);

            $writer->addRow(Row::fromValuesWithStyles([
                'No',
                'Nama Pabrikan',
                'Total SKU Aktif',
                'SKU Terjual',
                'Volume Terjual (Box/Fls)',
                'Total Omset (Rp)',
                'Kontribusi (%)',
            ], null, [
                0 => $styles['thCenter'],
                1 => $styles['thLeft'],
                2 => $styles['thCenter'],
                3 => $styles['thCenter'],
                4 => $styles['thRight'],
                5 => $styles['thRight'],
                6 => $styles['thRight'],
            ]));

            $no = 1;
            $totalSkus = 0;
            $totalSoldSkus = 0;

            foreach ($manufacturers as $m) {
                $agg = $mAggregates->get($m->id);
                $soldSkus = $agg ? (int) $agg->sold_skus : 0;
                $qty = $agg ? (int) $agg->total_qty : 0;
                $rev = $agg ? (float) $agg->total_revenue : 0.0;
                $pct = $totalGMV > 0 ? (($rev / $totalGMV) * 100) : 0.0;

                $totalSkus += (int) $m->products_count;
                $totalSoldSkus += $soldSkus;

                $writer->addRow(Row::fromValuesWithStyles([
                    $no++,
                    $m->name,
                    (int) $m->products_count,
                    $soldSkus,
                    $qty,
                    $rev,
                    number_format($pct, 2, ',', '.').'%',
                ], null, [
                    0 => $styles['tdCenter'],
                    1 => $styles['tdBoldLeft'],
                    2 => $styles['tdCenter'],
                    3 => $styles['tdCenter'],
                    4 => $styles['tdNumber'],
                    5 => $styles['tdBoldCurrency'],
                    6 => $styles['tdCenter'],
                ]));
            }

            $writer->addRow(Row::fromValuesWithStyles([
                '',
                'GRAND TOTAL',
                $totalSkus,
                $totalSoldSkus,
                $grandQty,
                $totalGMV,
                '100,00%',
            ], null, [
                0 => $styles['totalLeft'],
                1 => $styles['totalLeft'],
                2 => $styles['totalCenter'],
                3 => $styles['totalCenter'],
                4 => $styles['totalNumber'],
                5 => $styles['totalNumber'],
                6 => $styles['totalCenter'],
            ]));
        });
    }

    /**
     * 4. Export Leaderboard Report (.xlsx)
     */
    public function exportLeaderboard(): StreamedResponse
    {
        $gatheringService = app(GatheringService::class);
        $leaderboard = $gatheringService->getLeaderboard();
        $filename = 'Peringkat_Apotek_SMS_Gathering_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamXlsx($filename, function (Writer $writer, Options $options) use ($leaderboard) {
            $styles = $this->createStyles();

            $widths = [1 => 12, 2 => 14, 3 => 32, 4 => 18, 5 => 16, 6 => 18, 7 => 12, 8 => 22, 9 => 22, 10 => 22];
            foreach ($widths as $col => $w) {
                $options->setColumnWidth($w, $col);
            }

            $totalSpend = (float) $leaderboard->sum('total_spending');
            $totalOrders = (int) $leaderboard->sum('orders_count');
            $activeCount = $leaderboard->where('total_spending', '>', 0)->count();

            $summaryText = "Total Apotek: {$leaderboard->count()}  |  Apotek Bertransaksi: {$activeCount}  |  Total Akumulasi Belanja: Rp ".number_format($totalSpend, 0, ',', '.');
            $this->writeReportHeader($writer, $styles, 'LAPORAN PERINGKAT TRANSAKSI APOTEK (LEADERBOARD)', $summaryText);

            $writer->addRow(Row::fromValuesWithStyles([
                'Peringkat',
                'Kode Apotek',
                'Nama Apotek / Klinik',
                'Kota / Wilayah',
                'Kehadiran',
                'Waktu Check-In',
                'Jml PO',
                'Total Belanja (Rp)',
                'Order Pertama (Tie-Breaker)',
                'Order Terakhir',
            ], null, [
                0 => $styles['thCenter'],
                1 => $styles['thCenter'],
                2 => $styles['thLeft'],
                3 => $styles['thLeft'],
                4 => $styles['thCenter'],
                5 => $styles['thCenter'],
                6 => $styles['thCenter'],
                7 => $styles['thRight'],
                8 => $styles['thCenter'],
                9 => $styles['thCenter'],
            ]));

            foreach ($leaderboard as $item) {
                $rankDisplay = $item['total_spending'] > 0 ? '#'.$item['rank'] : '-';
                $firstOrderStr = $item['first_order_at'] ? Carbon::parse($item['first_order_at'])->format('d/m/Y H:i:s') : '-';
                $lastOrderStr = $item['last_order_at'] ? Carbon::parse($item['last_order_at'])->format('d/m/Y H:i:s') : '-';
                $checkinStr = $item['checked_in_at'] ? Carbon::parse($item['checked_in_at'])->format('d/m/Y H:i') : '-';

                $writer->addRow(Row::fromValuesWithStyles([
                    $rankDisplay,
                    $item['customer_code'],
                    $item['name'],
                    $item['city'] ?? '-',
                    $item['is_checked_in'] ? 'Hadir' : 'Belum Hadir',
                    $checkinStr,
                    (int) $item['orders_count'],
                    (float) $item['total_spending'],
                    $firstOrderStr,
                    $lastOrderStr,
                ], null, [
                    0 => $styles['tdBoldCenter'],
                    1 => $styles['tdCenter'],
                    2 => $styles['tdBoldLeft'],
                    3 => $styles['tdLeft'],
                    4 => $styles['tdCenter'],
                    5 => $styles['tdCenter'],
                    6 => $styles['tdCenter'],
                    7 => $styles['tdBoldCurrency'],
                    8 => $styles['tdCenter'],
                    9 => $styles['tdCenter'],
                ]));
            }

            $writer->addRow(Row::fromValuesWithStyles([
                '',
                '',
                'GRAND TOTAL',
                '',
                '',
                '',
                $totalOrders,
                $totalSpend,
                '',
                '',
            ], null, [
                0 => $styles['totalLeft'],
                1 => $styles['totalLeft'],
                2 => $styles['totalLeft'],
                3 => $styles['totalLeft'],
                4 => $styles['totalLeft'],
                5 => $styles['totalLeft'],
                6 => $styles['totalCenter'],
                7 => $styles['totalNumber'],
                8 => $styles['totalLeft'],
                9 => $styles['totalLeft'],
            ]));
        });
    }

    /**
     * 5. Export Pharmacy Orders Detail Report (.xlsx)
     */
    public function exportPharmacyOrders(): StreamedResponse
    {
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

        $filename = 'Rekap_Belanja_Seluruh_Apotek_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamXlsx($filename, function (Writer $writer, Options $options) use ($pharmacies) {
            $styles = $this->createStyles();

            $widths = [1 => 6, 2 => 14, 3 => 30, 4 => 18, 5 => 18, 6 => 26, 7 => 12, 8 => 30, 9 => 10, 10 => 16, 11 => 12, 12 => 18];
            foreach ($widths as $col => $w) {
                $options->setColumnWidth($w, $col);
            }

            $activePharmacies = $pharmacies->filter(fn ($p) => $p->orders->isNotEmpty());
            $grandTotal = (float) $activePharmacies->sum('total_spending');
            $grandQty = (int) $activePharmacies->sum(fn ($p) => $p->orders->sum('total_items'));

            $summaryText = "Apotek Berbelanja: {$activePharmacies->count()} Apotek  |  Total Item: ".number_format($grandQty, 0, ',', '.').'  |  Total Omset: Rp '.number_format($grandTotal, 0, ',', '.');
            $this->writeReportHeader($writer, $styles, 'LAPORAN REKAPITULASI BELANJA DETAIL PER APOTEK', $summaryText);

            $writer->addRow(Row::fromValuesWithStyles([
                'No',
                'Kode Apotek',
                'Nama Apotek / Klinik',
                'No. PO',
                'Waktu Order',
                'Pabrikan',
                'SKU',
                'Nama Produk',
                'Satuan',
                'Harga Satuan (Rp)',
                'Qty',
                'Subtotal (Rp)',
            ], null, [
                0 => $styles['thCenter'],
                1 => $styles['thCenter'],
                2 => $styles['thLeft'],
                3 => $styles['thCenter'],
                4 => $styles['thCenter'],
                5 => $styles['thLeft'],
                6 => $styles['thCenter'],
                7 => $styles['thLeft'],
                8 => $styles['thCenter'],
                9 => $styles['thRight'],
                10 => $styles['thRight'],
                11 => $styles['thRight'],
            ]));

            $no = 1;
            foreach ($activePharmacies as $pharmacy) {
                $firstRow = true;
                $pharmacyQty = 0;
                $pharmacyTotal = 0.0;
                $itemCount = 0;

                foreach ($pharmacy->orders as $order) {
                    foreach ($order->items as $item) {
                        $pharmacyQty += (int) $item->quantity;
                        $pharmacyTotal += (float) $item->subtotal;
                        $itemCount++;

                        $writer->addRow(Row::fromValuesWithStyles([
                            $firstRow ? $no++ : '',
                            $firstRow ? $pharmacy->customer_code : '',
                            $firstRow ? $pharmacy->name.($pharmacy->city ? " ({$pharmacy->city})" : '') : '',
                            $order->order_number,
                            $order->created_at->format('d/m/Y H:i'),
                            $item->manufacturer_name ?? ($item->product?->manufacturer?->name ?? 'Lainnya'),
                            $item->product?->sku ?? '-',
                            $item->product_name,
                            strtoupper($item->uom ?? $item->product?->uom ?? 'BOX'),
                            (float) $item->price,
                            (int) $item->quantity,
                            (float) $item->subtotal,
                        ], null, [
                            0 => $styles['tdCenter'],
                            1 => $styles['tdCenter'],
                            2 => $styles['tdBoldLeft'],
                            3 => $styles['tdCenter'],
                            4 => $styles['tdCenter'],
                            5 => $styles['tdLeft'],
                            6 => $styles['tdCenter'],
                            7 => $styles['tdLeft'],
                            8 => $styles['tdCenter'],
                            9 => $styles['tdCurrency'],
                            10 => $styles['tdNumber'],
                            11 => $styles['tdCurrency'],
                        ]));

                        $firstRow = false;
                    }
                }

                // Pharmacy Subtotal Row (like Inkaso report)
                $writer->addRow(Row::fromValuesWithStyles([
                    '',
                    '',
                    "Total {$pharmacy->name} ({$pharmacy->orders->count()} PO / {$itemCount} Item)",
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $pharmacyQty,
                    $pharmacyTotal,
                ], null, [
                    0 => $styles['subLeft'],
                    1 => $styles['subLeft'],
                    2 => $styles['subLeft'],
                    3 => $styles['subLeft'],
                    4 => $styles['subLeft'],
                    5 => $styles['subLeft'],
                    6 => $styles['subLeft'],
                    7 => $styles['subLeft'],
                    8 => $styles['subLeft'],
                    9 => $styles['subLeft'],
                    10 => $styles['subRight'],
                    11 => $styles['subRight'],
                ]));
            }

            $writer->addRow(Row::fromValuesWithStyles([
                '',
                '',
                'GRAND TOTAL SELURUH APOTEK',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $grandQty,
                $grandTotal,
            ], null, [
                0 => $styles['totalLeft'],
                1 => $styles['totalLeft'],
                2 => $styles['totalLeft'],
                3 => $styles['totalLeft'],
                4 => $styles['totalLeft'],
                5 => $styles['totalLeft'],
                6 => $styles['totalLeft'],
                7 => $styles['totalLeft'],
                8 => $styles['totalLeft'],
                9 => $styles['totalLeft'],
                10 => $styles['totalNumber'],
                11 => $styles['totalNumber'],
            ]));
        });
    }

    /**
     * 6. Export Single Pharmacy Detail & Handover (.xlsx)
     */
    public function exportSinglePharmacy(Pharmacy $pharmacy): StreamedResponse
    {
        $rewardService = app(RewardCalculationService::class);
        $orders = $pharmacy->orders()->where('status', '!=', 'cancelled')->with('items.product.manufacturer')->get();
        $rewards = $rewardService->calculateForPharmacy($pharmacy);
        $safeCode = Str::slug($pharmacy->customer_code ?: 'apotek');
        $filename = "Rincian_Belanja_{$safeCode}_".now()->format('Ymd_His').'.xlsx';

        return $this->streamXlsx($filename, function (Writer $writer, Options $options) use ($pharmacy, $orders, $rewards) {
            $styles = $this->createStyles();

            $widths = [1 => 6, 2 => 18, 3 => 28, 4 => 14, 5 => 32, 6 => 12, 7 => 18, 8 => 14, 9 => 20];
            foreach ($widths as $col => $w) {
                $options->setColumnWidth($w, $col);
            }

            $totalSpend = (float) $orders->sum('total_amount');
            $totalVoucher = (float) ($rewards['total_vouchers'] ?? 0);
            $summaryText = "Apotek: {$pharmacy->name} ({$pharmacy->customer_code})  |  Total Belanja: Rp ".number_format($totalSpend, 0, ',', '.').'  |  Total Voucher: Rp '.number_format($totalVoucher, 0, ',', '.');

            $this->writeReportHeader($writer, $styles, 'RINCIAN TRANSAKSI & PEROLEHAN HADIAH APOTEK', $summaryText);

            $writer->addRow(Row::fromValuesWithStyles([
                'No',
                'Nomor PO',
                'Pabrikan',
                'SKU',
                'Nama Produk',
                'Satuan',
                'Harga Satuan (Rp)',
                'Qty',
                'Subtotal (Rp)',
            ], null, [
                0 => $styles['thCenter'],
                1 => $styles['thCenter'],
                2 => $styles['thLeft'],
                3 => $styles['thCenter'],
                4 => $styles['thLeft'],
                5 => $styles['thCenter'],
                6 => $styles['thRight'],
                7 => $styles['thRight'],
                8 => $styles['thRight'],
            ]));

            $no = 1;
            $grandQty = 0;

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    $grandQty += (int) $item->quantity;

                    $writer->addRow(Row::fromValuesWithStyles([
                        $no++,
                        $order->order_number,
                        $item->manufacturer_name ?? ($item->product?->manufacturer?->name ?? '-'),
                        $item->product?->sku ?? '-',
                        $item->product_name,
                        strtoupper($item->uom ?? $item->product?->uom ?? 'BOX'),
                        (float) $item->price,
                        (int) $item->quantity,
                        (float) $item->subtotal,
                    ], null, [
                        0 => $styles['tdCenter'],
                        1 => $styles['tdCenter'],
                        2 => $styles['tdLeft'],
                        3 => $styles['tdCenter'],
                        4 => $styles['tdBoldLeft'],
                        5 => $styles['tdCenter'],
                        6 => $styles['tdCurrency'],
                        7 => $styles['tdNumber'],
                        8 => $styles['tdBoldCurrency'],
                    ]));
                }
            }

            $writer->addRow(Row::fromValuesWithStyles([
                '',
                '',
                'TOTAL BELANJA APOTEK',
                '',
                '',
                '',
                '',
                $grandQty,
                $totalSpend,
            ], null, [
                0 => $styles['totalLeft'],
                1 => $styles['totalLeft'],
                2 => $styles['totalLeft'],
                3 => $styles['totalLeft'],
                4 => $styles['totalLeft'],
                5 => $styles['totalLeft'],
                6 => $styles['totalLeft'],
                7 => $styles['totalNumber'],
                8 => $styles['totalNumber'],
            ]));
        });
    }
}
