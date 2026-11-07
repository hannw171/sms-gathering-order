<div style="border: 1px solid rgba(148, 163, 184, 0.22); border-radius: 10px; overflow: hidden; background: rgba(148, 163, 184, 0.04);">
    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr style="background: rgba(148, 163, 184, 0.1); border-bottom: 1px solid rgba(148, 163, 184, 0.18); color: #94a3b8; text-transform: uppercase; font-size: 10px; font-weight: 700; letter-spacing: 0.04em;">
                <th style="padding: 9px 14px; text-align: center; width: 48px;">No</th>
                <th style="padding: 9px 14px; text-align: left;">Program Sponsor / Pabrikan</th>
                <th style="padding: 9px 14px; text-align: left;">Nama Hadiah yang Dipilih</th>
                <th style="padding: 9px 14px; text-align: right; width: 130px;">Keterangan Kuota</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.1);">
                    <td style="padding: 10px 14px; text-align: center; color: #94a3b8; font-family: monospace;">
                        {{ $idx + 1 }}
                    </td>
                    <td style="padding: 10px 14px; font-weight: 600; color: #cbd5e1;">
                        🏢 {{ $item['program'] }}
                    </td>
                    <td style="padding: 10px 14px; font-weight: 700; color: #fbbf24;">
                        🎁 {{ $item['reward'] }}
                    </td>
                    <td style="padding: 10px 14px; text-align: right;">
                        <x-filament::badge color="info">
                            {{ $item['badge'] }}
                        </x-filament::badge>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
