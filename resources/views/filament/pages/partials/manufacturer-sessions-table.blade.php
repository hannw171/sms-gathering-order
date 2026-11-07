@php
    $manufacturers = \App\Models\Manufacturer::where('is_active', true)->orderBy('sort_order')->get();
@endphp

<div style="border: 1px solid rgba(148, 163, 184, 0.22); border-radius: 12px; overflow: hidden; background: rgba(148, 163, 184, 0.03); margin-top: 4px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
                <tr style="background: rgba(148, 163, 184, 0.08); border-bottom: 1px solid rgba(148, 163, 184, 0.2); color: #94a3b8; text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 0.04em;">
                    <th style="padding: 12px 16px; width: 30%;">Pabrikan</th>
                    <th style="padding: 12px 16px; width: 25%;">Status Sesi PO</th>
                    <th style="padding: 12px 16px; width: 20%;">Batas Waktu Sesi</th>
                    <th style="padding: 12px 16px; width: 25%; text-align: right;">Kontrol Cepat Sesi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manufacturers as $m)
                    @php
                        $isOpen = $m->isOrderingOpen();
                        $hasTimer = $m->session_end_at && $m->session_end_at->isFuture();
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.12); transition: background-color 0.15s ease;">
                        <td style="padding: 12px 16px; vertical-align: middle;">
                            <div style="font-weight: 700; font-size: 13px;">
                                {{ $m->name }}
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">
                                {{ $m->slug }}
                            </div>
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle;">
                            @if(! $m->is_ordering_active)
                                <x-filament::badge color="danger">
                                    🛑 Ditutup Manual
                                </x-filament::badge>
                            @elseif($m->session_end_at && now()->gt($m->session_end_at))
                                <x-filament::badge color="warning">
                                    ⏱️ Waktu Selesai
                                </x-filament::badge>
                            @elseif($hasTimer)
                                <x-filament::badge color="success">
                                    🔥 Flash Sesi (Sisa {{ $m->session_end_at->diffForHumans(['parts' => 1, 'short' => true]) }})
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="success">
                                    🟢 Buka (Tanpa Batas)
                                </x-filament::badge>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle; font-family: monospace; font-size: 12px;">
                            @if($m->session_end_at)
                                <span style="font-weight: 600;">{{ $m->session_end_at->format('H:i') }} WIB</span>
                                <span style="color: #94a3b8; font-size: 11px;">({{ $m->session_end_at->format('d/m') }})</span>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Tanpa batas waktu</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle; text-align: right;">
                            <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                @if($isOpen)
                                    <x-filament::button
                                        size="xs"
                                        color="warning"
                                        icon="heroicon-m-plus"
                                        wire:click="extendManufacturer({{ $m->id }}, 15)"
                                    >
                                        +15 Menit
                                    </x-filament::button>
                                    <x-filament::button
                                        size="xs"
                                        color="danger"
                                        icon="heroicon-m-lock-closed"
                                        wire:click="closeManufacturer({{ $m->id }})"
                                    >
                                        Tutup Sesi
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        size="xs"
                                        color="success"
                                        icon="heroicon-m-play"
                                        wire:click="openManufacturerWithDuration({{ $m->id }}, 30)"
                                    >
                                        Buka (30m)
                                    </x-filament::button>
                                    <x-filament::button
                                        size="xs"
                                        color="info"
                                        icon="heroicon-m-clock"
                                        wire:click="openManufacturerUnlimited({{ $m->id }})"
                                    >
                                        Buka Penuh
                                    </x-filament::button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
