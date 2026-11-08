@extends('reports.layout', [
    'reportTitle' => 'LAPORAN REKAPITULASI PENJUALAN PER PRODUK (SKU)',
    'defaultOrientation' => 'portrait',
])

@section('content')
    {{-- Summary Strip --}}
    <div class="summary-strip">
        <div class="summary-item primary">
            <div class="summary-label">TOTAL OMSET PRODUK</div>
            <div class="summary-value">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                <span class="summary-sub">({{ $soldSkuCount }} SKU Terjual)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">TOTAL VOLUME TERJUAL</div>
            <div class="summary-value">
                {{ number_format($totalVolume, 0, ',', '.') }}
                <span class="summary-sub">(Box / Fls / Btl)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">PENYERAPAN KATALOG</div>
            <div class="summary-value">
                {{ $soldSkuCount }} / {{ $totalSkus }} SKU
                <span class="summary-sub">({{ $totalSkus > 0 ? number_format(($soldSkuCount / $totalSkus) * 100, 1, ',', '.') : 0 }}%)</span>
            </div>
        </div>
    </div>

    {{-- Section Banner --}}
    <div class="section-banner green">
        <span>TABEL 1: DAFTAR REKAPITULASI PRODUK TERJUAL (DIURUTKAN DARI QTY TERBANYAK)</span>
        <span>Total Omset: Rp {{ number_format($totalRevenue, 0, ',', '.') }} ({{ number_format($totalVolume, 0, ',', '.') }} Unit)</span>
    </div>

    <table class="report-table green-head">
        <thead>
            <tr>
                <th style="width: 32px;" class="text-center">No</th>
                <th style="width: 78px;" class="text-center">SKU</th>
                <th>Nama Produk</th>
                <th style="width: 24%;">Pabrikan</th>
                <th style="width: 55px;" class="text-center">Satuan</th>
                <th style="width: 100px;" class="text-right">Harga Satuan</th>
                <th style="width: 75px;" class="text-right">Qty Terjual</th>
                <th style="width: 115px;" class="text-right">Total Omset</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $idx => $prod)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-mono">{{ $prod->sku }}</td>
                    <td class="font-bold">{{ $prod->name }}</td>
                    <td>{{ $prod->manufacturer?->name ?? '-' }}</td>
                    <td class="text-center">{{ strtoupper($prod->uom ?? $prod->unit ?? 'BOX') }}</td>
                    <td class="text-right">Rp {{ number_format($prod->price, 0, ',', '.') }}</td>
                    <td class="text-right font-bold {{ $prod->qty_sold > 0 ? 'text-green' : 'text-muted' }}">
                        {{ number_format($prod->qty_sold, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-bold {{ $prod->total_revenue > 0 ? '' : 'text-muted' }}">
                        Rp {{ number_format($prod->total_revenue, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 20px;">Tidak ada data katalog produk.</td>
                </tr>
            @endforelse
        </tbody>
        @if($products->isNotEmpty())
            <tfoot>
                <tr class="row-grandtotal">
                    <td colspan="6" class="text-right">GRAND TOTAL PENJUALAN PRODUK ({{ $soldSkuCount }} SKU TERJUAL)</td>
                    <td class="text-right">{{ number_format($totalVolume, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="signature-grid">
        <div class="signature-box">
            <div>Dibuat / Dicetak Oleh,</div>
            <div class="signature-line">Admin / Panitia Gathering</div>
        </div>
        <div class="signature-box">
            <div>Mengetahui / Menyetujui,</div>
            <div class="signature-line">Pimpinan PT. Satria Medika Sejahtera</div>
        </div>
    </div>
@endsection
