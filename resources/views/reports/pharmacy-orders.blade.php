@extends('reports.layout', [
    'reportTitle' => 'LAPORAN REKAPITULASI BELANJA DETAIL PER APOTEK',
    'defaultOrientation' => 'portrait',
])

@section('content')
    {{-- Summary Strip --}}
    <div class="summary-strip">
        <div class="summary-item primary">
            <div class="summary-label">TOTAL BELANJA SELURUH APOTEK</div>
            <div class="summary-value">
                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                <span class="summary-sub">({{ $totalPoCount }} Nota PO)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">APOTEK BERTRANSAKSI</div>
            <div class="summary-value">
                {{ $pharmacies->count() }} Apotek
                <span class="summary-sub">(dari {{ $allPharmaciesCount }} terdaftar)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">TOTAL VOLUME PRODUK</div>
            <div class="summary-value">
                {{ number_format($totalItemQty, 0, ',', '.') }}
                <span class="summary-sub">(Box / Fls / Btl)</span>
            </div>
        </div>
    </div>

    {{-- Section Banner --}}
    <div class="section-banner green">
        <span>TABEL 1: RINCIAN PEMBELIAN PRODUK PER APOTEK / PELANGGAN</span>
        <span>Total Omset: Rp {{ number_format($grandTotal, 0, ',', '.') }} ({{ $pharmacies->count() }} Apotek)</span>
    </div>

    <table class="report-table green-head">
        <thead>
            <tr>
                <th style="width: 32px;" class="text-center">No</th>
                <th style="width: 22%;">Nama Apotek / Pelanggan</th>
                <th style="width: 14%;">No. PO & Waktu</th>
                <th style="width: 18%;">Pabrikan</th>
                <th>Nama Produk</th>
                <th style="width: 65px;" class="text-right">Qty</th>
                <th style="width: 105px;" class="text-right">Harga Satuan</th>
                <th style="width: 115px;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pharmacies as $index => $pharmacy)
                @php
                    $allItems = $pharmacy->orders->flatMap(function ($order) {
                        return $order->items->map(function ($item) use ($order) {
                            $item->order_number = $order->order_number;
                            $item->order_time = $order->created_at->format('d M Y, H:i');
                            return $item;
                        });
                    });
                    $rowCount = $allItems->count() + 1;
                    $pharmacySubtotal = (float) $allItems->sum('subtotal');
                    $pharmacyQty = (int) $allItems->sum('quantity');
                @endphp

                @foreach($allItems as $itemIdx => $item)
                    <tr>
                        @if($itemIdx === 0)
                            <td rowspan="{{ $rowCount }}" class="text-center font-bold">{{ $index + 1 }}</td>
                            <td rowspan="{{ $rowCount }}">
                                <div class="font-bold">{{ $pharmacy->name }}</div>
                                <div class="text-muted font-mono">{{ $pharmacy->customer_code }} • {{ $pharmacy->city ?? '-' }}</div>
                            </td>
                        @endif
                        <td>
                            <div class="font-mono font-bold">{{ $item->order_number }}</div>
                            <div class="text-muted" style="font-size: 9.5px;">{{ $item->order_time }}</div>
                        </td>
                        <td>{{ $item->manufacturer_name ?? ($item->product?->manufacturer?->name ?? '-') }}</td>
                        <td>
                            <span class="font-bold">{{ $item->product_name }}</span>
                            @if($item->product?->sku)
                                <span class="text-muted font-mono">({{ $item->product->sku }})</span>
                            @endif
                        </td>
                        <td class="text-right font-bold">
                            {{ number_format($item->quantity, 0, ',', '.') }}
                            <span class="text-muted" style="font-size: 9px;">{{ strtoupper($item->uom ?? $item->product?->uom ?? 'BOX') }}</span>
                        </td>
                        <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- Subtotal Row per Pharmacy (sfa_phar Inkaso style) --}}
                <tr class="row-subtotal">
                    <td colspan="3">
                        Total {{ $pharmacy->orders->count() }} PO ({{ $allItems->count() }} Item Produk)
                    </td>
                    <td class="text-right">{{ number_format($pharmacyQty, 0, ',', '.') }} Unit</td>
                    <td></td>
                    <td class="text-right subtotal-amount">Rp {{ number_format($pharmacySubtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 20px;">Belum ada data transaksi apotek.</td>
                </tr>
            @endforelse
        </tbody>
        @if($pharmacies->isNotEmpty())
            <tfoot>
                <tr class="row-grandtotal">
                    <td colspan="5" class="text-right">GRAND TOTAL SELURUH APOTEK ({{ $pharmacies->count() }} APOTEK / {{ $totalPoCount }} PO)</td>
                    <td class="text-right">{{ number_format($totalItemQty, 0, ',', '.') }} Unit</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
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
