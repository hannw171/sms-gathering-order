@extends('reports.layout', [
    'reportTitle' => 'LAPORAN REKAPITULASI PENERIMA HADIAH & VOUCHER APOTEK',
    'defaultOrientation' => 'landscape',
])

@section('content')
    {{-- Summary Strip --}}
    <div class="summary-strip">
        <div class="summary-item primary">
            <div class="summary-label">TOTAL BELANJA APOTEK</div>
            <div class="summary-value">
                Rp {{ number_format($totalSpending, 0, ',', '.') }}
                <span class="summary-sub">({{ $summaries->count() }} Apotek)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">TOTAL VOUCHER BELANJA</div>
            <div class="summary-value">
                Rp {{ number_format($totalVouchers, 0, ',', '.') }}
                <span class="summary-sub">(Akumulasi Pabrikan)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">APOTEK BERHAK HADIAH</div>
            <div class="summary-value">
                {{ $rewardedCount }} Apotek
                <span class="summary-sub">(Memenuhi Syarat)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">STATUS SERAH TERIMA</div>
            <div class="summary-value">
                {{ $handedOverCount }} / {{ $rewardedCount }}
                <span class="summary-sub">(Sudah Diserahkan)</span>
            </div>
        </div>
    </div>

    {{-- Table 1: Pharmacy Reward List --}}
    <div class="section-banner green">
        <span>TABEL 1: DAFTAR APOTEK PENERIMA HADIAH FISIK, PILIHAN & VOUCHER BELANJA</span>
        <span>Total Voucher: Rp {{ number_format($totalVouchers, 0, ',', '.') }} ({{ $rewardedCount }} Apotek)</span>
    </div>

    <table class="report-table green-head">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th style="width: 20%;">Nama Apotek / Pelanggan</th>
                <th style="width: 115px;" class="text-right">Total Belanja</th>
                <th style="width: 20%;">Hadiah Langsung (Fisik)</th>
                <th style="width: 20%;">Hadiah Pilihan Apotek</th>
                <th style="width: 16%;">Breakdown Voucher</th>
                <th style="width: 105px;" class="text-right">Total Voucher</th>
                <th style="width: 130px;">Status & Penerima</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summaries as $idx => $item)
                @php
                    $pharmacy = $item['pharmacy'];
                    $rewards = $item['rewards'];
                    $physicalRewards = $rewards['physical_rewards'] ?? [];
                    $choiceSelections = $rewards['choice_selections'] ?? [];
                    $voucherBreakdown = $rewards['voucher_breakdown'] ?? [];
                    $totalVoucher = (float) ($rewards['vouchers'] ?? 0);
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $idx + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $pharmacy->name }}</div>
                        <div class="text-muted font-mono">{{ $pharmacy->customer_code }} • {{ $pharmacy->city ?? '-' }} ({{ $item['order_count'] }} PO)</div>
                    </td>
                    <td class="text-right font-bold">Rp {{ number_format($item['total_spending'], 0, ',', '.') }}</td>
                    <td>
                        @if(!empty($physicalRewards))
                            @foreach($physicalRewards as $giftName => $qty)
                                <div>• {{ is_numeric($giftName) ? $qty : "{$giftName}: {$qty} pcs" }}</div>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($choiceSelections))
                            @foreach($choiceSelections as $cs)
                                <div>• <strong>{{ $cs['selected_option'] ?? '-' }}</strong> <span class="text-muted">({{ strtoupper($cs['manufacturer_slug'] ?? '') }})</span></div>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @php $hasV = false; @endphp
                        @foreach($voucherBreakdown as $slug => $nom)
                            @if($nom > 0)
                                @php $hasV = true; @endphp
                                <div>{{ strtoupper($slug) }}: Rp {{ number_format($nom, 0, ',', '.') }}</div>
                            @endif
                        @endforeach
                        @if(!$hasV)
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-right font-bold text-green">
                        Rp {{ number_format($totalVoucher, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($item['is_handed_over'])
                            <span class="badge badge-green">✓ Diserahkan</span>
                            <div class="font-bold" style="margin-top: 2px;">{{ $item['receiver_name'] ?? '-' }}</div>
                            <div class="text-muted" style="font-size: 9px;">{{ $item['handed_over_at']?->format('d/m/Y H:i') }}</div>
                        @else
                            <span class="badge badge-amber">Belum Diserahkan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 20px;">Belum ada data penerima hadiah.</td>
                </tr>
            @endforelse
        </tbody>
        @if($summaries->isNotEmpty())
            <tfoot>
                <tr class="row-grandtotal">
                    <td colspan="2" class="text-right">GRAND TOTAL KESELURUHAN</td>
                    <td class="text-right">Rp {{ number_format($totalSpending, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                    <td class="text-right">Rp {{ number_format($totalVouchers, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Table 2: Itemized Physical & Choice Rewards Summary --}}
    @if(!empty($itemRecap))
        <div class="section-banner red" style="margin-top: 18px;">
            <span>TABEL 2: REKAPITULASI KEBUTUHAN BARANG HADIAH FISIK & PILIHAN</span>
            <span>Total Jenis Item: {{ count($itemRecap) }} Jenis Hadiah</span>
        </div>

        <table class="report-table red-head">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">No</th>
                    <th style="width: 28%;">Kategori Hadiah</th>
                    <th>Nama Barang / Unit Hadiah</th>
                    <th style="width: 140px;" class="text-right">Total Kebutuhan (Unit)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalUnitHadiah = 0; @endphp
                @foreach($itemRecap as $idx => $rItem)
                    @php $totalUnitHadiah += (int) $rItem['qty']; @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $rItem['type'] }}</td>
                        <td class="font-bold">{{ $rItem['name'] }}</td>
                        <td class="text-right font-bold">{{ number_format($rItem['qty'], 0, ',', '.') }} Pcs / Unit</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="row-grandtotal">
                    <td colspan="3" class="text-right">TOTAL SELURUH BARANG HADIAH</td>
                    <td class="text-right">{{ number_format($totalUnitHadiah, 0, ',', '.') }} Pcs / Unit</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="signature-grid">
        <div class="signature-box">
            <div>Dibuat / Dicetak Oleh,</div>
            <div class="signature-line">Admin / Koordinator Hadiah</div>
        </div>
        <div class="signature-box">
            <div>Mengetahui / Menyetujui,</div>
            <div class="signature-line">Pimpinan PT. Satria Medika Sejahtera</div>
        </div>
    </div>
@endsection
