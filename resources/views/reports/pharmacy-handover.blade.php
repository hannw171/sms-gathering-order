@extends('reports.layout', [
    'reportTitle' => 'RINCIAN PESANAN & BERITA ACARA SERAH TERIMA HADIAH',
    'rightMetaTop' => 'Pelanggan: ' . $pharmacy->name . ' (' . $pharmacy->customer_code . ')',
    'defaultOrientation' => 'portrait',
    'excelUrl' => route('admin.reports.pharmacy-detail.excel', $pharmacy),
])

@section('content')
    {{-- Customer Info Strip --}}
    <div style="background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 6px; padding: 8px 12px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 10.5px;">
        <div>
            <strong>Apotek / Klinik:</strong> {{ $pharmacy->name }} ({{ $pharmacy->customer_code }})
            &nbsp;|&nbsp;
            <strong>Kota:</strong> {{ $pharmacy->city ?? '-' }}
            @if($pharmacy->owner_name)
                &nbsp;|&nbsp; <strong>PIC:</strong> {{ $pharmacy->owner_name }}
            @endif
        </div>
        <div>
            <strong>No. PO:</strong>
            <span class="font-mono font-bold">{{ $orders->pluck('order_number')->implode(', ') ?: '-' }}</span>
        </div>
    </div>

    {{-- Summary Strip --}}
    <div class="summary-strip">
        <div class="summary-item primary">
            <div class="summary-label">TOTAL BELANJA APOTEK</div>
            <div class="summary-value">
                Rp {{ number_format($totalSpending, 0, ',', '.') }}
                <span class="summary-sub">({{ $orders->count() }} Nota PO)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">TOTAL VOLUME BARANG</div>
            <div class="summary-value">
                {{ number_format($totalQty, 0, ',', '.') }} Unit
                <span class="summary-sub">({{ $itemsByManufacturer->count() }} Pabrikan)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">TOTAL VOUCHER BELANJA</div>
            <div class="summary-value">
                Rp {{ number_format($rewards['total_vouchers'] ?? 0, 0, ',', '.') }}
                <span class="summary-sub">(Potongan / Voucher)</span>
            </div>
        </div>
    </div>

    {{-- Table 1: Order Items Grouped by Manufacturer --}}
    <div class="section-banner green">
        <span>TABEL 1: RINCIAN PEMBELIAN PRODUK PER PABRIKAN</span>
        <span>Total Belanja: Rp {{ number_format($totalSpending, 0, ',', '.') }} ({{ $orders->count() }} Nota PO)</span>
    </div>

    <table class="report-table green-head">
        <thead>
            <tr>
                <th style="width: 32px;" class="text-center">No</th>
                <th style="width: 24%;">Nama Pabrikan</th>
                <th style="width: 78px;" class="text-center">SKU</th>
                <th>Nama Produk</th>
                <th style="width: 55px;" class="text-center">Satuan</th>
                <th style="width: 95px;" class="text-right">Harga Satuan</th>
                <th style="width: 65px;" class="text-right">Qty</th>
                <th style="width: 115px;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemsByManufacturer as $mIndex => $mGroup)
                @php
                    $items = $mGroup['items'];
                    $rowCount = $items->count() + 1;
                @endphp
                @foreach($items as $iIdx => $item)
                    <tr>
                        @if($iIdx === 0)
                            <td rowspan="{{ $rowCount }}" class="text-center font-bold">{{ $mIndex + 1 }}</td>
                            <td rowspan="{{ $rowCount }}" class="font-bold">{{ $mGroup['manufacturer_name'] }}</td>
                        @endif
                        <td class="text-center font-mono">{{ $item['sku'] }}</td>
                        <td class="font-bold">{{ $item['name'] }}</td>
                        <td class="text-center">{{ $item['uom'] }}</td>
                        <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold">{{ number_format($item['total_qty'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item['total_subtotal'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="row-subtotal">
                    <td colspan="4">
                        Total {{ $items->count() }} Produk ({{ $mGroup['manufacturer_name'] }})
                        @if(($mGroup['voucher'] ?? 0) > 0)
                            <span class="badge badge-green" style="margin-left: 6px;">+ Voucher Rp {{ number_format($mGroup['voucher'], 0, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($mGroup['total_qty'], 0, ',', '.') }}</td>
                    <td class="text-right subtotal-amount">Rp {{ number_format($mGroup['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 18px;">Belum ada item pesanan untuk apotek ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($itemsByManufacturer->isNotEmpty())
            <tfoot>
                <tr class="row-grandtotal">
                    <td colspan="6" class="text-right">TOTAL KESELURUHAN PEMBELIAN ({{ $orders->count() }} PO)</td>
                    <td class="text-right">{{ number_format($totalQty, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalSpending, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Table 2: Rewards & Vouchers Verification Table --}}
    @php
        $rewardRows = [];
        foreach (($rewards['physical_rewards'] ?? []) as $giftName => $qty) {
            $rewardRows[] = [
                'category' => 'Hadiah Fisik Langsung',
                'source' => 'Program Pabrikan',
                'description' => is_numeric($giftName) ? (string) $qty : (string) $giftName,
                'qty_or_val' => is_numeric($giftName) ? '1 Unit' : "{$qty} Pcs / Unit",
            ];
        }
        foreach (($rewards['choice_selections'] ?? []) as $cs) {
            $rewardRows[] = [
                'category' => 'Hadiah Pilihan Apotek',
                'source' => strtoupper($cs['manufacturer_slug'] ?? 'Program Pilihan'),
                'description' => $cs['selected_option'] ?? '-',
                'qty_or_val' => '1 Unit',
            ];
        }
        foreach (($rewards['vouchers_by_manufacturer'] ?? []) as $slug => $vAmt) {
            if ($vAmt > 0) {
                $rewardRows[] = [
                    'category' => 'Voucher Belanja',
                    'source' => strtoupper(str_replace('-', ' ', $slug)),
                    'description' => 'Voucher Belanja Gathering ' . strtoupper(str_replace('-', ' ', $slug)),
                    'qty_or_val' => 'Rp ' . number_format($vAmt, 0, ',', '.'),
                ];
            }
        }
    @endphp

    <div class="section-banner red" style="margin-top: 16px;">
        <span>TABEL 2: DAFTAR HAK PEROLEHAN HADIAH & VOUCHER BELANJA (BERITA ACARA SERAH TERIMA)</span>
        <span>Total Voucher: Rp {{ number_format($rewards['total_vouchers'] ?? 0, 0, ',', '.') }} ({{ count($rewardRows) }} Item Reward)</span>
    </div>

    <table class="report-table red-head">
        <thead>
            <tr>
                <th style="width: 34px;" class="text-center">No</th>
                <th style="width: 22%;">Kategori Reward</th>
                <th style="width: 22%;">Program / Pabrikan</th>
                <th>Nama Hadiah / Keterangan Voucher</th>
                <th style="width: 120px;" class="text-right">Jumlah / Nominal</th>
                <th style="width: 75px;" class="text-center">Ceklis</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rewardRows as $idx => $rRow)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $rRow['category'] }}</td>
                    <td class="font-bold">{{ $rRow['source'] }}</td>
                    <td class="font-bold">{{ $rRow['description'] }}</td>
                    <td class="text-right font-bold">{{ $rRow['qty_or_val'] }}</td>
                    <td class="text-center font-mono">[ &nbsp;&nbsp; ] OK</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 14px;">Belum ada perolehan hadiah fisik atau voucher yang memenuhi syarat tier.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Handover Statement & Signatures --}}
    <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 6px; padding: 10px 14px; margin-top: 12px; font-size: 10px; color: #334155;">
        <strong>Pernyataan Serah Terima:</strong>
        Rekapitulasi pesanan Purchase Order (PO) beserta perolehan hadiah dan voucher belanja di atas telah diperiksa bersama dan diserahterimakan dengan lengkap dan benar.
        @if($pharmacy->reward_handed_over_at)
            <div style="margin-top: 4px; color: #047857; font-weight: 700;">
                ✓ Status Sistem: Sudah diserahkan pada {{ $pharmacy->reward_handed_over_at->format('d/m/Y H:i') }} WIB kepada {{ $pharmacy->reward_receiver_name ?? '-' }}
                @if($pharmacy->reward_handover_notes)
                    (Catatan: {{ $pharmacy->reward_handover_notes }})
                @endif
            </div>
        @endif
    </div>

    <div class="signature-grid" style="margin-top: 20px;">
        <div class="signature-box">
            <div>Diserahkan Oleh,</div>
            <div class="text-muted" style="font-size: 9.5px;">Panitia PT Satria Medika Sejahtera</div>
            <div class="signature-line">( ............................................ )</div>
        </div>
        <div class="signature-box">
            <div>Diterima Oleh,</div>
            <div class="text-muted" style="font-size: 9.5px;">Perwakilan {{ $pharmacy->name }}</div>
            <div class="signature-line">
                {{ $pharmacy->reward_receiver_name ?: ($pharmacy->owner_name ?: '( ............................................ )') }}
            </div>
        </div>
    </div>
@endsection
