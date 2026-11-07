@php
    $manufacturers = \App\Models\Manufacturer::where('is_active', true)->orderBy('sort_order')->get();
@endphp

<div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider">
            <tr>
                <th class="py-2.5 px-3.5">Pabrikan</th>
                <th class="py-2.5 px-3.5">Status Sesi PO</th>
                <th class="py-2.5 px-3.5">Batas Waktu Sesi</th>
                <th class="py-2.5 px-3.5 text-right">Kontrol Cepat Sesi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
            @foreach($manufacturers as $m)
                @php
                    $isOpen = $m->isOrderingOpen();
                    $hasTimer = $m->session_end_at && $m->session_end_at->isFuture();
                @endphp
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                    <td class="py-2.5 px-3.5 font-bold text-sm text-gray-900 dark:text-white">
                        <div class="flex items-center gap-2">
                            <span>{{ $m->name }}</span>
                            <span class="text-[10px] text-gray-400 font-mono">({{ $m->slug }})</span>
                        </div>
                    </td>
                    <td class="py-2.5 px-3.5">
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
                    <td class="py-2.5 px-3.5 font-mono text-[11px] text-gray-600 dark:text-gray-400">
                        @if($m->session_end_at)
                            {{ $m->session_end_at->format('H:i') }} WIB ({{ $m->session_end_at->format('d/m') }})
                        @else
                            <span class="text-gray-400 italic">Tanpa batas waktu</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($isOpen)
                                <button
                                    type="button"
                                    wire:click="extendManufacturer({{ $m->id }}, 15)"
                                    class="px-2 py-1 text-[11px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 rounded-md border border-amber-200 dark:border-amber-800 transition-colors"
                                    title="Tambah 15 menit ke hitung mundur"
                                >
                                    +15 Menit
                                </button>
                                <button
                                    type="button"
                                    wire:click="closeManufacturer({{ $m->id }})"
                                    class="px-2 py-1 text-[11px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 rounded-md border border-rose-200 dark:border-rose-800 transition-colors"
                                    title="Kunci / tutup sesi pemesanan pabrikan ini"
                                >
                                    Tutup Sesi
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click="openManufacturerWithDuration({{ $m->id }}, 30)"
                                    class="px-2 py-1 text-[11px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 rounded-md border border-emerald-200 dark:border-emerald-800 transition-colors"
                                    title="Buka sesi 30 menit"
                                >
                                    Buka (30m)
                                </button>
                                <button
                                    type="button"
                                    wire:click="openManufacturerUnlimited({{ $m->id }})"
                                    class="px-2 py-1 text-[11px] font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-md border border-blue-200 dark:border-blue-800 transition-colors"
                                    title="Buka tanpa batas waktu"
                                >
                                    Buka Penuh
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
