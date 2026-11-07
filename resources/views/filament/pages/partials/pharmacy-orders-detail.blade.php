@php
    $rewardService = app(\App\Services\RewardCalculationService::class);
    $orders = $pharmacy->orders()->where('status', '!=', 'cancelled')->with('items.product.manufacturer')->get();
    $totalSpending = (float) $orders->sum('total_amount');
    $rewards = $rewardService->calculateForPharmacy($pharmacy);

    // Group items by manufacturer
    $allItems = $orders->flatMap->items;
    $itemsByManufacturer = $allItems->groupBy(function ($item) {
        return $item->manufacturer_name ?? ($item->product?->manufacturer?->name ?? 'Pabrikan Lainnya');
    })->map(function ($items, $mName) use ($rewards) {
        $first = $items->first();
        $mSlug = $first->product?->manufacturer?->slug ?? \Illuminate\Support\Str::slug($mName);
        $mVoucher = (float) ($rewards['vouchers_by_manufacturer'][$mSlug] ?? ($rewards['details'][$mSlug]['vouchers'] ?? 0));

        $aggregated = $items->groupBy('product_name')->map(function ($prodItems) {
            $first = $prodItems->first();
            return [
                'sku' => $first->product?->sku ?? '-',
                'name' => $first->product_name,
                'unit' => $first->product?->unit ?? 'box',
                'price' => (float) $first->price,
                'total_qty' => (int) $prodItems->sum('quantity'),
                'total_subtotal' => (float) $prodItems->sum('subtotal'),
            ];
        });

        return [
            'manufacturer_name' => $mName,
            'manufacturer_slug' => $mSlug,
            'voucher' => $mVoucher,
            'items' => $aggregated,
            'subtotal' => (float) $items->sum('subtotal'),
            'total_qty' => (int) $items->sum('quantity'),
        ];
    });
@endphp

<div class="space-y-6 text-sm">
    {{-- Pharmacy Quick Profile Header --}}
    <div style="background: rgba(148, 163, 184, 0.08); border-radius: 12px; padding: 16px; border: 1px solid rgba(148, 163, 184, 0.2);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 4px 0;">{{ $pharmacy->name }}</h3>
                <div style="font-size: 12px; color: #94a3b8;">
                    <strong>{{ $pharmacy->customer_code }}</strong> &bull; {{ $pharmacy->city ?? '-' }} &bull; PIC: {{ $pharmacy->sales_pic ?? '-' }} &bull; Telp: {{ $pharmacy->phone ?? '-' }}
                </div>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                @if($pharmacy->is_checked_in)
                    <x-filament::badge color="success">✓ Hadir di Gathering</x-filament::badge>
                @else
                    <x-filament::badge color="gray">Belum Hadir</x-filament::badge>
                @endif
                <div style="display: flex; gap: 6px; margin-top: 2px;">
                    <a
                        href="{{ route('admin.reports.pharmacy-detail.excel', $pharmacy) }}"
                        style="display: inline-flex; align-items: center; gap: 4px; background: #059669; color: #fff; font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 6px; text-decoration: none;"
                    >
                        📥 Excel (.xlsx)
                    </a>
                    <a
                        href="{{ route('admin.reports.pharmacy-detail.print', $pharmacy) }}"
                        target="_blank"
                        style="display: inline-flex; align-items: center; gap: 4px; background: #1e293b; color: #f8fafc; border: 1px solid rgba(148, 163, 184, 0.3); font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 6px; text-decoration: none;"
                    >
                        🖨️ Cetak Berita Acara A4
                    </a>
                </div>
            </div>
        </div>

        {{-- Metrics Grid --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 12px;">
            <div style="background: rgba(148, 163, 184, 0.1); padding: 10px; border-radius: 8px;">
                <span style="font-size: 10px; text-transform: uppercase; color: #94a3b8; display: block; font-weight: 600;">Total Belanja</span>
                <span style="font-size: 14px; font-weight: 800; color: #10b981;">
                    Rp {{ number_format($totalSpending, 0, ',', '.') }}
                </span>
            </div>

            <div style="background: rgba(148, 163, 184, 0.1); padding: 10px; border-radius: 8px;">
                <span style="font-size: 10px; text-transform: uppercase; color: #94a3b8; display: block; font-weight: 600;">Jumlah PO</span>
                <span style="font-size: 14px; font-weight: 800;">
                    {{ $orders->count() }} Order
                </span>
            </div>

            <div style="background: rgba(148, 163, 184, 0.1); padding: 10px; border-radius: 8px;">
                <span style="font-size: 10px; text-transform: uppercase; color: #94a3b8; display: block; font-weight: 600;">Total Voucher</span>
                <span style="font-size: 14px; font-weight: 800; color: #38bdf8;">
                    Rp {{ number_format($rewards['total_vouchers'] ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Rewards Row --}}
        @php
            $voucherBreakdown = $rewards['vouchers_by_manufacturer'] ?? [];
        @endphp
        @if(!empty($rewards['physical_rewards']) || !empty($rewards['choice_selections']) || !empty($voucherBreakdown))
            <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed rgba(148, 163, 184, 0.3); display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #f59e0b; margin-right: 4px;">Hadiah & Voucher:</span>
                @foreach(($rewards['physical_rewards'] ?? []) as $giftName => $qty)
                    <x-filament::badge color="warning">🎁 {{ is_numeric($giftName) ? $qty : $giftName . ': ' . $qty . ' pcs' }}</x-filament::badge>
                @endforeach
                @foreach(($rewards['choice_selections'] ?? []) as $cs)
                    <x-filament::badge color="info">✨ {{ $cs['selected_option'] ?? '' }} ({{ $cs['manufacturer_slug'] ?? '' }})</x-filament::badge>
                @endforeach
                @foreach($voucherBreakdown as $slug => $vAmt)
                    @if($vAmt > 0)
                        <x-filament::badge color="success">🎟️ Voucher {{ ucwords(str_replace('-', ' ', $slug)) }}: Rp {{ number_format($vAmt, 0, ',', '.') }}</x-filament::badge>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Breakdown per Pabrikan --}}
    @if($itemsByManufacturer->isNotEmpty())
        <div class="space-y-4">
            <h4 style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">
                Rincian Pembelian Per Pabrikan:
            </h4>

            @foreach($itemsByManufacturer as $mGroup)
                <div style="border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 10px; overflow: hidden; margin-bottom: 12px;">
                    {{-- Pabrikan Header --}}
                    <div style="background: rgba(148, 163, 184, 0.08); padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(148, 163, 184, 0.15); flex-wrap: wrap; gap: 8px;">
                        <span style="font-weight: 700; font-size: 12px; text-transform: uppercase;">
                            🏢 {{ $mGroup['manufacturer_name'] }}
                        </span>
                        <div style="font-size: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: #94a3b8;">Volume: <strong>{{ number_format($mGroup['total_qty'], 0, ',', '.') }}</strong></span>
                            <strong style="color: #10b981;">Rp {{ number_format($mGroup['subtotal'], 0, ',', '.') }}</strong>
                            @if(($mGroup['voucher'] ?? 0) > 0)
                                <x-filament::badge color="success">
                                    🎟️ Voucher: Rp {{ number_format($mGroup['voucher'], 0, ',', '.') }}
                                </x-filament::badge>
                            @endif
                        </div>
                    </div>

                    {{-- Table of Products --}}
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.1); color: #94a3b8; text-transform: uppercase; font-size: 10px;">
                                <th style="padding: 6px 12px; text-align: left;">SKU</th>
                                <th style="padding: 6px 12px; text-align: left;">Nama Produk</th>
                                <th style="padding: 6px 12px; text-align: center;">Satuan</th>
                                <th style="padding: 6px 12px; text-align: right;">Harga</th>
                                <th style="padding: 6px 12px; text-align: right;">Qty</th>
                                <th style="padding: 6px 12px; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mGroup['items'] as $item)
                                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.06);">
                                    <td style="padding: 6px 12px; font-family: monospace; color: #94a3b8;">{{ $item['sku'] }}</td>
                                    <td style="padding: 6px 12px; font-weight: 600;">{{ $item['name'] }}</td>
                                    <td style="padding: 6px 12px; text-align: center; text-transform: uppercase; font-size: 10px; color: #94a3b8;">{{ $item['unit'] }}</td>
                                    <td style="padding: 6px 12px; text-align: right;">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td style="padding: 6px 12px; text-align: right; font-weight: 700; color: #38bdf8;">{{ number_format($item['total_qty'], 0, ',', '.') }}</td>
                                    <td style="padding: 6px 12px; text-align: right; font-weight: 700;">Rp {{ number_format($item['total_subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        {{-- Purchase Order List --}}
        @if($orders->isNotEmpty())
            <div style="margin-top: 16px;">
                <h4 style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 8px;">
                    Daftar PO yang Diterbitkan (Klik untuk Buka Detail PO):
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px;">
                    @foreach($orders as $o)
                        <a 
                            href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $o->id]) }}"
                            target="_blank"
                            style="text-decoration: none; color: inherit; display: block;"
                        >
                            <div style="padding: 10px; border-radius: 8px; border: 1px solid rgba(148, 163, 184, 0.2); background: rgba(148, 163, 184, 0.05); font-size: 11px; transition: all 0.2s;" class="hover:border-primary-500 hover:bg-primary-50/10">
                                <div style="display: flex; justify-content: space-between; font-weight: 700;">
                                    <span style="color: #38bdf8; font-family: monospace;">{{ $o->order_number }} ↗</span>
                                    <span style="color: #94a3b8;">{{ $o->created_at->format('H:i') }} WIB</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                                    <span style="color: #94a3b8;">{{ $o->total_items }} Item</span>
                                    <span style="font-weight: 700;">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</span>
                                </div>
                                @if($o->notes)
                                    <div style="margin-top: 6px; padding-top: 4px; border-top: 1px dashed rgba(148, 163, 184, 0.2); font-size: 10px; color: #94a3b8; font-style: italic;">
                                        Catatan: {{ $o->notes }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div style="text-align: center; padding: 40px 20px; border: 1px dashed rgba(148, 163, 184, 0.3); border-radius: 12px; color: #94a3b8;">
            <div style="font-size: 32px; margin-bottom: 8px;">📦</div>
            <div style="font-weight: 600; font-size: 14px;">Belum Ada Transaksi</div>
            <div style="font-size: 12px; margin-top: 4px;">Apotek ini belum melakukan pemesanan pada gathering malam ini.</div>
        </div>
    @endif
</div>
