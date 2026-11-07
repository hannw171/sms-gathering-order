@extends('reports.layout', [
    'reportTitle' => 'LAPORAN PERINGKAT TRANSAKSI APOTEK (LEADERBOARD)',
    'defaultOrientation' => 'portrait',
])

@section('content')
    {{-- Summary Strip --}}
    <div class="summary-strip">
        <div class="summary-item primary">
            <div class="summary-label">TOTAL AKUMULASI BELANJA</div>
            <div class="summary-value">
                Rp {{ number_format($totalSpending, 0, ',', '.') }}
                <span class="summary-sub">({{ $totalOrders }} Nota PO)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">APOTEK BERTRANSAKSI</div>
            <div class="summary-value">
                {{ $activePharmacies->count() }} Apotek
                <span class="summary-sub">(dari {{ $leaderboard->count() }} terdaftar)</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">KEHADIRAN PESERTA</div>
            <div class="summary-value">
                {{ $checkedInCount }} / {{ $leaderboard->count() }}
                <span class="summary-sub">(Apotek Hadir)</span>
            </div>
        </div>
    </div>

    {{-- Table 1: Overall Leaderboard --}}
    <div class="section-banner green">
        <span>TABEL 1: PERINGKAT AKUMULASI TRANSAKSI APOTEK (TERBESAR KE TERKECIL)</span>
        <span>Total Transaksi: Rp {{ number_format($totalSpending, 0, ',', '.') }} ({{ $activePharmacies->count() }} Apotek)</span>
    </div>

    <table class="report-table green-head">
        <thead>
            <tr>
                <th style="width: 55px;" class="text-center">Rank</th>
                <th style="width: 75px;" class="text-center">Kode</th>
                <th>Nama Apotek / Klinik</th>
                <th style="width: 14%;">Kota / Wilayah</th>
                <th style="width: 78px;" class="text-center">Kehadiran</th>
                <th style="width: 60px;" class="text-center">Jml PO</th>
                <th style="width: 125px;" class="text-right">Total Belanja</th>
                <th style="width: 90px;" class="text-center">Order Pertama</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaderboard as $item)
                @php
                    $hasSpend = $item['total_spending'] > 0;
                    $rank = $item['rank'] ?? null;
                @endphp
                <tr>
                    <td class="text-center font-bold">
                        @if($hasSpend)
                            @if($rank === 1)
                                <span class="badge badge-amber">🥇 #1</span>
                            @elseif($rank === 2)
                                <span class="badge badge-gray">🥈 #2</span>
                            @elseif($rank === 3)
                                <span class="badge badge-amber">🥉 #3</span>
                            @else
                                #{{ $rank }}
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center font-mono">{{ $item['customer_code'] }}</td>
                    <td class="font-bold">{{ $item['name'] }}</td>
                    <td>{{ $item['city'] ?? '-' }}</td>
                    <td class="text-center">
                        @if($item['is_checked_in'])
                            <span class="badge badge-green">Hadir</span>
                        @else
                            <span class="text-muted">Belum</span>
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ $item['orders_count'] }}</td>
                    <td class="text-right font-bold {{ $hasSpend ? 'text-green' : 'text-muted' }}">
                        Rp {{ number_format($item['total_spending'], 0, ',', '.') }}
                    </td>
                    <td class="text-center font-mono text-muted">
                        {{ $item['first_order_at'] ? \Carbon\Carbon::parse($item['first_order_at'])->format('H:i:s') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 20px;">Belum ada data peringkat apotek.</td>
                </tr>
            @endforelse
        </tbody>
        @if($leaderboard->isNotEmpty())
            <tfoot>
                <tr class="row-grandtotal">
                    <td colspan="5" class="text-right">GRAND TOTAL SELURUH APOTEK</td>
                    <td class="text-center">{{ $totalOrders }} PO</td>
                    <td class="text-right">Rp {{ number_format($totalSpending, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Table 2: Graha Farma Grand Prize Standings --}}
    @if($grahaStandings->isNotEmpty())
        <div class="section-banner red" style="margin-top: 18px;">
            <span>TABEL 2: KLASEMEN GRAND PRIZE PT. GRAHA FARMA (BERDASARKAN JUMLAH PAKET TERBANYAK)</span>
            <span>Top {{ $grahaStandings->count() }} Apotek</span>
        </div>

        <table class="report-table red-head">
            <thead>
                <tr>
                    <th style="width: 55px;" class="text-center">Juara</th>
                    <th style="width: 80px;" class="text-center">Kode</th>
                    <th>Nama Apotek / Pelanggan</th>
                    <th style="width: 110px;" class="text-right">Total Box/Paket</th>
                    <th style="width: 130px;" class="text-right">Nominal Graha</th>
                    <th style="width: 28%;">Hadiah Grand Prize Elektronik</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grahaStandings as $g)
                    <tr>
                        <td class="text-center font-bold">#{{ $g['rank'] }}</td>
                        <td class="text-center font-mono">{{ $g['customer_code'] }}</td>
                        <td class="font-bold">{{ $g['name'] }} <span class="text-muted">({{ $g['city'] ?? '-' }})</span></td>
                        <td class="text-right font-bold">
                            {{ $g['paket_count'] }} Paket
                            <span class="text-muted" style="font-size: 9px;">({{ number_format($g['reguler_box'], 0, ',', '.') }} Box)</span>
                        </td>
                        <td class="text-right font-bold">Rp {{ number_format($g['total_spending'], 0, ',', '.') }}</td>
                        <td class="font-bold">
                            @if(!empty($g['grand_prize']))
                                🏆 {{ $g['grand_prize'] }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signature-grid">
        <div class="signature-box">
            <div>Dibuat / Dicetak Oleh,</div>
            <div class="signature-line">Admin / Panitia Gathering</div>
        </div>
        <div class="signature-box">
            <div>Mengetahui / Menyetujui,</div>
            <div class="signature-line">Pimpinan PT Satria Medika Sejahtera</div>
        </div>
    </div>
@endsection
