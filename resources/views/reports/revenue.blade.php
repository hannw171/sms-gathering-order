@extends('reports.layout', [
    'reportTitle' => 'LAPORAN REKAPITULASI OMSET & GMV PER PABRIKAN',
    'defaultOrientation' => 'portrait',
])

@section('content')
    {{-- Summary Strip --}}
    <div class="summary-strip">
        <div class="summary-item primary">
            <div class="summary-label">TOTAL GMV GATHERING</div>
            <div class="summary-value">
                Rp {{ number_format($totalGMV, 0, ',', '.') }}
                <span class="summary-sub">(Seluruh Pabrikan)</span>
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
            <div class="summary-label">PARTISIPASI PABRIKAN</div>
            <div class="summary-value">
                {{ $manufacturers->count() }} Pabrikan
                <span class="summary-sub">({{ $totalSoldSkus }} / {{ $totalSkus }} SKU Terjual)</span>
            </div>
        </div>
    </div>

    {{-- Section Banner --}}
    <div class="section-banner green">
        <span>TABEL 1: RINCIAN PEROLEHAN OMSET & KONTRIBUSI PER PABRIKAN</span>
        <span>Total GMV: Rp {{ number_format($totalGMV, 0, ',', '.') }} (100%)</span>
    </div>

    <table class="report-table green-head">
        <thead>
            <tr>
                <th style="width: 34px;" class="text-center">No</th>
                <th>Nama Pabrikan</th>
                <th style="width: 80px;" class="text-center">SKU Aktif</th>
                <th style="width: 80px;" class="text-center">SKU Terjual</th>
                <th style="width: 85px;" class="text-center">Apotek Beli</th>
                <th style="width: 100px;" class="text-right">Volume (Unit)</th>
                <th style="width: 130px;" class="text-right">Total Omset</th>
                <th style="width: 85px;" class="text-right">Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($manufacturers as $idx => $m)
                <tr>
                    <td class="text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $m->name }}</td>
                    <td class="text-center">{{ $m->products_count }}</td>
                    <td class="text-center font-bold">{{ $m->sold_skus }}</td>
                    <td class="text-center">{{ $m->pharmacy_count }} Apotek</td>
                    <td class="text-right font-bold">{{ number_format($m->total_qty, 0, ',', '.') }}</td>
                    <td class="text-right font-bold text-green">Rp {{ number_format($m->total_revenue, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($m->contribution, 1, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 20px;">Belum ada data pabrikan.</td>
                </tr>
            @endforelse
        </tbody>
        @if($manufacturers->isNotEmpty())
            <tfoot>
                <tr class="row-grandtotal">
                    <td colspan="2" class="text-right">GRAND TOTAL OMSET ACARA</td>
                    <td class="text-center">{{ $totalSkus }}</td>
                    <td class="text-center">{{ $totalSoldSkus }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($totalVolume, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalGMV, 0, ',', '.') }}</td>
                    <td class="text-right">100,0%</td>
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
