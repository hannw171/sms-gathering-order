<div style="border: 1px solid rgba(148, 163, 184, 0.22); border-radius: 10px; overflow: hidden; background: rgba(148, 163, 184, 0.04);">
    {{-- Table Header Bar --}}
    <div style="background: rgba(148, 163, 184, 0.1); padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(148, 163, 184, 0.18);">
        <span style="font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.03em;">
            📦 Daftar Produk dalam PO Ini
        </span>
        <x-filament::badge color="primary">
            {{ $items->count() }} Macam Produk &bull; {{ number_format($order->total_items, 0, ',', '.') }} Total Qty
        </x-filament::badge>
    </div>

    {{-- Items Table --}}
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.15); color: #94a3b8; text-transform: uppercase; font-size: 10px; font-weight: 700; letter-spacing: 0.04em;">
                    <th style="padding: 8px 12px; text-align: center; width: 44px;">No</th>
                    <th style="padding: 8px 12px; text-align: left; width: 90px;">SKU</th>
                    <th style="padding: 8px 12px; text-align: left;">Nama Produk</th>
                    <th style="padding: 8px 12px; text-align: left;">Pabrikan</th>
                    <th style="padding: 8px 12px; text-align: center; width: 70px;">Satuan</th>
                    <th style="padding: 8px 12px; text-align: right; width: 120px;">Harga Satuan</th>
                    <th style="padding: 8px 12px; text-align: right; width: 70px;">Qty</th>
                    <th style="padding: 8px 12px; text-align: right; width: 140px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                    <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.08);">
                        <td style="padding: 9px 12px; text-align: center; font-family: monospace; color: #94a3b8;">
                            {{ $idx + 1 }}
                        </td>
                        <td style="padding: 9px 12px; font-family: monospace; color: #94a3b8;">
                            {{ $item->product?->sku ?? '-' }}
                        </td>
                        <td style="padding: 9px 12px; font-weight: 700;">
                            {{ $item->product_name }}
                        </td>
                        <td style="padding: 9px 12px; color: #cbd5e1;">
                            {{ $item->manufacturer_name ?? ($item->product?->manufacturer?->name ?? '-') }}
                        </td>
                        <td style="padding: 9px 12px; text-align: center; text-transform: uppercase; font-size: 10px; color: #94a3b8; font-weight: 600;">
                            {{ $item->uom }}
                        </td>
                        <td style="padding: 9px 12px; text-align: right;">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </td>
                        <td style="padding: 9px 12px; text-align: right; font-weight: 700; color: #38bdf8;">
                            {{ number_format($item->quantity, 0, ',', '.') }}
                        </td>
                        <td style="padding: 9px 12px; text-align: right; font-weight: 700;">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: rgba(148, 163, 184, 0.08); border-top: 2px solid rgba(148, 163, 184, 0.2); font-weight: 800;">
                    <td colspan="6" style="padding: 10px 12px; text-align: right; text-transform: uppercase; font-size: 11px; color: #94a3b8;">
                        Total Pesanan PO Ini:
                    </td>
                    <td style="padding: 10px 12px; text-align: right; color: #38bdf8; font-size: 13px;">
                        {{ number_format($order->total_items, 0, ',', '.') }}
                    </td>
                    <td style="padding: 10px 12px; text-align: right; color: #10b981; font-size: 13px;">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
