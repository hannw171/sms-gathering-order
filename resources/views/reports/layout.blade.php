<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle ?? 'Laporan Gathering' }} - PT SATRIA MEDIKA SEJAHTERA</title>
    <style id="page-orientation-style">
        @page {
            size: A4 {{ $defaultOrientation ?? 'portrait' }};
            margin: 10mm 12mm;
        }
    </style>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #111827;
            background-color: #E2E8F0;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Screen Toolbar */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0F172A;
            color: #F8FAFC;
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar-title {
            font-weight: 700;
            font-size: 13px;
            color: #FFFFFF;
        }

        .toolbar-badge {
            background: #1E293B;
            color: #94A3B8;
            border: 1px solid #334155;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-outline {
            background: #1E293B;
            color: #E2E8F0;
            border-color: #475569;
        }

        .btn-outline:hover, .btn-outline.active {
            background: #334155;
            border-color: #38BDF8;
            color: #38BDF8;
        }

        .btn-primary {
            background: #059669;
            color: #FFFFFF;
        }

        .btn-primary:hover {
            background: #047857;
        }

        /* Paper Container on Screen */
        .page-sheet {
            background: #FFFFFF;
            max-width: 210mm;
            margin: 20px auto;
            padding: 14mm 15mm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        body.is-landscape .page-sheet {
            max-width: 297mm;
        }

        /* Report Header (sfa_phar style) */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid #1565C0;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: #0D47A1;
            letter-spacing: 0.3px;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 12.5px;
            font-weight: 800;
            color: #111827;
            margin: 0;
            text-transform: uppercase;
        }

        .header-right {
            text-align: right;
        }

        .meta-top {
            font-size: 11.5px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px 0;
        }

        .meta-date {
            font-size: 10.5px;
            color: #4B5563;
            margin: 0;
        }

        /* Summary Strip Box */
        .summary-strip {
            display: flex;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .summary-item {
            flex: 1;
            padding: 9px 14px;
            border-right: 1px solid #E2E8F0;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748B;
            margin-bottom: 3px;
            letter-spacing: 0.2px;
        }

        .summary-item.primary .summary-label {
            color: #0D47A1;
        }

        .summary-value {
            font-size: 14.5px;
            font-weight: 800;
            color: #111827;
        }

        .summary-item.primary .summary-value {
            color: #0D47A1;
        }

        .summary-sub {
            font-size: 10px;
            font-weight: 400;
            color: #64748B;
            margin-left: 3px;
        }

        /* Section Banner above Table */
        .section-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .section-banner.green {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        .section-banner.red {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        .section-banner.blue {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            color: #1E40AF;
        }

        /* Data Table */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10.5px;
        }

        table.report-table thead {
            display: table-header-group;
        }

        table.report-table tr {
            page-break-inside: avoid;
        }

        table.report-table th {
            padding: 6px 8px;
            font-weight: 700;
            color: #FFFFFF;
            border: 1px solid #065F46;
            text-align: left;
        }

        table.report-table.green-head th {
            background-color: #047857;
            border-color: #065F46;
        }

        table.report-table.red-head th {
            background-color: #B91C1C;
            border-color: #991B1B;
        }

        table.report-table.blue-head th {
            background-color: #1D4ED8;
            border-color: #1E40AF;
        }

        table.report-table td {
            padding: 5px 8px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
            color: #1E293B;
        }

        table.report-table tbody tr:nth-child(even) td.zebra {
            background-color: #F8FAFC;
        }

        .row-subtotal td {
            background-color: #F8FAFC !important;
            font-weight: 700;
            color: #111827;
        }

        .row-subtotal td.subtotal-amount {
            background-color: #ECFDF5 !important;
            color: #047857 !important;
            font-weight: 800;
        }

        .row-grandtotal td {
            background-color: #EFF6FF !important;
            font-weight: 800;
            color: #1E3A8A !important;
            border-top: 2px solid #64748B;
            border-bottom: 2px solid #64748B;
            padding: 7px 8px;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: 700 !important; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 10px; }
        .text-green { color: #047857 !important; }
        .text-blue { color: #0D47A1 !important; }
        .text-muted { color: #64748B !important; }

        .badge {
            display: inline-block;
            padding: 1.5px 6px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 700;
        }
        .badge-green { background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; }
        .badge-amber { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
        .badge-blue { background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; }
        .badge-gray { background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; }

        /* Signature Footer Block */
        .signature-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 220px;
            text-align: center;
            font-size: 10.5px;
        }

        .signature-line {
            margin-top: 52px;
            border-bottom: 1px solid #334155;
            font-weight: 700;
            padding-bottom: 3px;
        }

        @media print {
            body {
                background: #FFFFFF !important;
            }
            .no-print {
                display: none !important;
            }
            .page-sheet {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="{{ ($defaultOrientation ?? 'portrait') === 'landscape' ? 'is-landscape' : '' }}">
    <div class="print-toolbar no-print">
        <div class="toolbar-left">
            <span class="toolbar-title">📄 {{ $reportTitle ?? 'Cetak Laporan A4' }}</span>
            <span class="toolbar-badge">Format Kertas A4 Resmi</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" id="btn-portrait" class="btn btn-outline {{ ($defaultOrientation ?? 'portrait') === 'portrait' ? 'active' : '' }}" onclick="setOrientation('portrait')">
                ↕️ A4 Portrait
            </button>
            <button type="button" id="btn-landscape" class="btn btn-outline {{ ($defaultOrientation ?? 'portrait') === 'landscape' ? 'active' : '' }}" onclick="setOrientation('landscape')">
                ↔️ A4 Landscape
            </button>
            @if(!empty($excelUrl))
                <a href="{{ $excelUrl }}" class="btn btn-outline">
                    📥 Download Excel (.xlsx)
                </a>
            @endif
            <button type="button" class="btn btn-primary" onclick="window.print()">
                🖨️ Cetak / Simpan PDF
            </button>
            <button type="button" class="btn btn-outline" onclick="window.close()">
                ✕ Tutup
            </button>
        </div>
    </div>

    <div class="page-sheet">
        <div class="report-header">
            <div>
                <h1 class="company-name">PT SATRIA MEDIKA SEJAHTERA</h1>
                <h2 class="report-title">{{ $reportTitle ?? 'LAPORAN REKAPITULASI GATHERING' }}</h2>
            </div>
            <div class="header-right">
                <p class="meta-top">{{ $rightMetaTop ?? ('Acara: ' . ($event->event_name ?? 'SMS Gathering & Order Night 2026')) }}</p>
                <p class="meta-date">Tanggal: {{ now()->translatedFormat('l, d F Y - H:i') }} WIB</p>
            </div>
        </div>

        @yield('content')
    </div>

    <script>
        function setOrientation(mode) {
            const styleEl = document.getElementById('page-orientation-style');
            styleEl.innerHTML = `@page { size: A4 ${mode}; margin: 10mm 12mm; }`;
            if (mode === 'landscape') {
                document.body.classList.add('is-landscape');
                document.getElementById('btn-landscape').classList.add('active');
                document.getElementById('btn-portrait').classList.remove('active');
            } else {
                document.body.classList.remove('is-landscape');
                document.getElementById('btn-portrait').classList.add('active');
                document.getElementById('btn-landscape').classList.remove('active');
            }
        }
    </script>
</body>
</html>
