<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan Reservasi - {{ $monthName }} {{ $year }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: a4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Header / Kop Laporan */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .clinic-brand {
            font-size: 18px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .clinic-tagline {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .clinic-address {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        .report-badge {
            display: inline-block;
            background-color: #f0fdfa;
            color: #0d9488;
            border: 1px solid #99f6e4;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .report-period {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .report-printed {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Section Titles */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 10px;
        }

        /* KPI Cards Grid */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-left: -8px;
            margin-right: -8px;
            margin-bottom: 14px;
        }

        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: top;
            width: 25%;
        }

        .kpi-card-teal { border-top: 3px solid #0d9488; }
        .kpi-card-emerald { border-top: 3px solid #10b981; }
        .kpi-card-rose { border-top: 3px solid #f43f5e; }
        .kpi-card-blue { border-top: 3px solid #3b82f6; }

        .kpi-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
            margin-bottom: 2px;
        }

        .kpi-sub {
            font-size: 8.5px;
            color: #64748b;
        }

        /* Charts Container */
        .chart-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }

        .chart-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 8px;
        }

        /* Segmented Status Bar */
        .segmented-bar {
            width: 100%;
            height: 14px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #e2e8f0;
            margin-bottom: 8px;
        }

        .segmented-bar table {
            width: 100%;
            height: 14px;
            border-collapse: collapse;
        }

        .bar-seg {
            height: 14px;
            padding: 0;
            margin: 0;
        }

        /* Horizontal Bars for Services & Doctors */
        .bar-row {
            margin-bottom: 6px;
        }

        .bar-label-table {
            width: 100%;
            margin-bottom: 2px;
        }

        .bar-label-name {
            font-size: 9.5px;
            font-weight: bold;
            color: #1e293b;
        }

        .bar-label-stat {
            font-size: 9px;
            color: #64748b;
            text-align: right;
            font-family: monospace;
        }

        .progress-track {
            width: 100%;
            height: 8px;
            background-color: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill-teal {
            height: 8px;
            background-color: #0d9488;
            border-radius: 4px;
        }

        .progress-fill-emerald {
            height: 8px;
            background-color: #10b981;
            border-radius: 4px;
        }

        /* Legend */
        .legend-table {
            width: 100%;
            margin-top: 6px;
        }

        .legend-item {
            font-size: 8.5px;
            color: #475569;
        }

        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }

        .data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.3px;
            padding: 6px 8px;
            border: 1px solid #0f766e;
            text-align: left;
        }

        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            color: #1e293b;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .data-table .text-center { text-align: center; }
        .data-table .text-right { text-align: right; }
        .data-table .font-bold { font-weight: bold; }
        .data-table .font-mono { font-family: monospace; font-size: 9px; }

        .badge-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-danger { background-color: #ffe4e6; color: #be123c; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .badge-gray { background-color: #f1f5f9; color: #475569; }

        /* Signatures */
        .signature-table {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-col {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 50px;
        }

        /* Footer */
        .footer-note {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- 1. Header & Kop Klinik -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="clinic-brand">{{ $clinic->clinic_name ?? 'Klinik Gigi Senyum Sehat' }}</div>
                <div class="clinic-tagline">{{ $clinic->tagline ?? 'Klinik Gigi Modern, Nyaman, dan Terpercaya' }}</div>
                <div class="clinic-address">
                    {{ $clinic->address ?? 'Jl. Merdeka No. 123, Jakarta' }} • WA: +{{ $clinic->whatsapp_number ?? '6281234567890' }}
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="report-badge">Laporan Eksekutif Bulanan</div>
                <div class="report-period">{{ $monthName }} {{ $year }}</div>
                <div class="report-printed">Dicetak pada: {{ $printedAt }}</div>
            </td>
        </tr>
    </table>

    <!-- 2. Ringkasan KPI Utama -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card kpi-card-teal">
                <div class="kpi-label">Total Reservasi</div>
                <div class="kpi-value">{{ $summary['total_bookings'] }}</div>
                <div class="kpi-sub">{{ $summary['pending'] }} Pending • {{ $summary['verified'] }} Terverifikasi</div>
            </td>
            <td class="kpi-card kpi-card-emerald">
                <div class="kpi-label">Kunjungan Selesai</div>
                <div class="kpi-value">{{ $summary['completed'] }}</div>
                <div class="kpi-sub">Omset: Rp {{ number_format($summary['estimated_revenue'], 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card kpi-card-rose">
                <div class="kpi-label">Angka No-Show</div>
                <div class="kpi-value" style="color: {{ $summary['no_show'] > 0 ? '#e11d48' : '#0f172a' }};">
                    {{ $summary['no_show'] }}
                </div>
                <div class="kpi-sub">Rasio: <strong>{{ $summary['no_show_rate'] }}%</strong> dari total</div>
            </td>
            <td class="kpi-card kpi-card-blue">
                <div class="kpi-label">Tingkat Kehadiran</div>
                <div class="kpi-value">{{ $summary['attendance_rate'] }}%</div>
                <div class="kpi-sub">Batal: {{ $summary['cancelled'] }} • Expired: {{ $summary['expired'] }}</div>
            </td>
        </tr>
    </table>

    <!-- 3. Visual Charts -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-left: -8px; margin-right: -8px; margin-bottom: 12px;">
        <tr>
            <!-- Chart 1: Distribusi Status Reservasi -->
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-box">
                    <div class="chart-title">Grafik 1: Distribusi Status Reservasi</div>

                    @php
                        $total = max(1, $summary['total_bookings']);
                        $pctCompleted = round(($summary['completed'] / $total) * 100, 1);
                        $pctVerified = round(($summary['verified'] / $total) * 100, 1);
                        $pctPending = round(($summary['pending'] / $total) * 100, 1);
                        $pctCancelled = round(($summary['cancelled'] / $total) * 100, 1);
                        $pctExpired = round(($summary['expired'] / $total) * 100, 1);
                        $pctNoShow = round(($summary['no_show'] / $total) * 100, 1);
                    @endphp

                    <!-- Segmented Bar -->
                    <div class="segmented-bar">
                        <table>
                            <tr>
                                @if ($pctCompleted > 0)
                                    <td class="bar-seg" style="width: {{ $pctCompleted }}%; background-color: #10b981;" title="Selesai: {{ $summary['completed'] }}"></td>
                                @endif
                                @if ($pctVerified > 0)
                                    <td class="bar-seg" style="width: {{ $pctVerified }}%; background-color: #3b82f6;" title="Terverifikasi: {{ $summary['verified'] }}"></td>
                                @endif
                                @if ($pctPending > 0)
                                    <td class="bar-seg" style="width: {{ $pctPending }}%; background-color: #f59e0b;" title="Pending: {{ $summary['pending'] }}"></td>
                                @endif
                                @if ($pctCancelled > 0)
                                    <td class="bar-seg" style="width: {{ $pctCancelled }}%; background-color: #94a3b8;" title="Batal: {{ $summary['cancelled'] }}"></td>
                                @endif
                                @if ($pctExpired > 0)
                                    <td class="bar-seg" style="width: {{ $pctExpired }}%; background-color: #cbd5e1;" title="Expired: {{ $summary['expired'] }}"></td>
                                @endif
                                @if ($pctNoShow > 0)
                                    <td class="bar-seg" style="width: {{ $pctNoShow }}%; background-color: #f43f5e;" title="No-Show: {{ $summary['no_show'] }}"></td>
                                @endif
                            </tr>
                        </table>
                    </div>

                    <!-- Legend -->
                    <table class="legend-table">
                        <tr>
                            <td class="legend-item"><span class="legend-dot" style="background-color: #10b981;"></span> Selesai ({{ $summary['completed'] }})</td>
                            <td class="legend-item"><span class="legend-dot" style="background-color: #3b82f6;"></span> Terverifikasi ({{ $summary['verified'] }})</td>
                            <td class="legend-item"><span class="legend-dot" style="background-color: #f59e0b;"></span> Pending ({{ $summary['pending'] }})</td>
                        </tr>
                        <tr>
                            <td class="legend-item"><span class="legend-dot" style="background-color: #94a3b8;"></span> Batal ({{ $summary['cancelled'] }})</td>
                            <td class="legend-item"><span class="legend-dot" style="background-color: #cbd5e1;"></span> Expired ({{ $summary['expired'] }})</td>
                            <td class="legend-item"><span class="legend-dot" style="background-color: #f43f5e;"></span> No-Show ({{ $summary['no_show'] }})</td>
                        </tr>
                    </table>
                </div>
            </td>

            <!-- Chart 2: Kontribusi Layanan -->
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-box">
                    <div class="chart-title">Grafik 2: Pangsa & Kontribusi Layanan</div>

                    @foreach ($serviceStats as $srv)
                        <div class="bar-row">
                            <table class="bar-label-table">
                                <tr>
                                    <td class="bar-label-name">{{ $srv['name'] }}</td>
                                    <td class="bar-label-stat">{{ $srv['total'] }} bkg ({{ $srv['share_percentage'] }}%)</td>
                                </tr>
                            </table>
                            <div class="progress-track">
                                <div class="progress-fill-teal" style="width: {{ max(4, $srv['share_percentage']) }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    <!-- 4. Tabel Rekapitulasi per Layanan -->
    <div class="section-title">Rekapitulasi Reservasi per Layanan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Layanan</th>
                <th style="width: 75px;" class="text-right">Tarif Dasar</th>
                <th style="width: 45px;" class="text-center">Total</th>
                <th style="width: 45px;" class="text-center">Selesai</th>
                <th style="width: 60px;" class="text-center">Batal/Exp</th>
                <th style="width: 50px;" class="text-center">No-Show</th>
                <th style="width: 85px;" class="text-right">Estimasi Omset</th>
                <th style="width: 55px;" class="text-center">Pangsa</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBookings = 0;
                $totCompleted = 0;
                $totCancelled = 0;
                $totNoShow = 0;
                $totRevenue = 0;
            @endphp
            @foreach ($serviceStats as $idx => $srv)
                @php
                    $totBookings += $srv['total'];
                    $totCompleted += $srv['completed'];
                    $totCancelled += $srv['cancelled_or_expired'];
                    $totNoShow += $srv['no_show'];
                    $totRevenue += $srv['revenue'];
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $srv['name'] }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($srv['starting_price'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ $srv['total'] }}</td>
                    <td class="text-center"><span class="badge-pill badge-success">{{ $srv['completed'] }}</span></td>
                    <td class="text-center">{{ $srv['cancelled_or_expired'] }}</td>
                    <td class="text-center">
                        @if ($srv['no_show'] > 0)
                            <span class="badge-pill badge-danger">{{ $srv['no_show'] }}</span>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($srv['revenue'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ $srv['share_percentage'] }}%</td>
                </tr>
            @endforeach
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ $totBookings }}</td>
                <td class="text-center">{{ $totCompleted }}</td>
                <td class="text-center">{{ $totCancelled }}</td>
                <td class="text-center">{{ $totNoShow }}</td>
                <td class="text-right font-mono">Rp {{ number_format($totRevenue, 0, ',', '.') }}</td>
                <td class="text-center">100%</td>
            </tr>
        </tbody>
    </table>

    <!-- 5. Tabel Rekapitulasi per Dokter -->
    <div class="section-title">Rekapitulasi Reservasi per Dokter Praktek</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Dokter</th>
                <th>Spesialisasi / Gelar</th>
                <th style="width: 55px;" class="text-center">Total Pasien</th>
                <th style="width: 55px;" class="text-center">Selesai</th>
                <th style="width: 60px;" class="text-center">Batal/Exp</th>
                <th style="width: 55px;" class="text-center">No-Show</th>
                <th style="width: 75px;" class="text-center">Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($doctorStats as $idx => $doc)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $doc['name'] }}</td>
                    <td style="color: #64748b;">{{ $doc['title'] }}</td>
                    <td class="text-center font-bold">{{ $doc['total'] }}</td>
                    <td class="text-center"><span class="badge-pill badge-success">{{ $doc['completed'] }}</span></td>
                    <td class="text-center">{{ $doc['cancelled_or_expired'] }}</td>
                    <td class="text-center">
                        @if ($doc['no_show'] > 0)
                            <span class="badge-pill badge-danger">{{ $doc['no_show'] }}</span>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge-pill {{ $doc['attendance_rate'] >= 80 ? 'badge-success' : 'badge-warning' }}">
                            {{ $doc['attendance_rate'] }}%
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 6. Daftar Pasien No-Show & Pembatalan (Jika ada) -->
    @if (count($noShowList) > 0)
        <div class="section-title">Daftar Pasien Tidak Hadir (No-Show) & Dibatalkan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">No</th>
                    <th style="width: 65px;">Kode</th>
                    <th>Nama Pasien</th>
                    <th style="width: 80px;">WhatsApp</th>
                    <th>Layanan</th>
                    <th>Dokter</th>
                    <th style="width: 95px;">Jadwal</th>
                    <th style="width: 65px;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($noShowList as $idx => $ns)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold" style="color: #0f766e;">{{ $ns['code'] }}</td>
                        <td class="font-bold">{{ $ns['patient_name'] }}</td>
                        <td class="font-mono">{{ $ns['patient_whatsapp'] }}</td>
                        <td>{{ $ns['service_name'] }}</td>
                        <td>{{ $ns['doctor_name'] }}</td>
                        <td style="font-size: 8.5px;">{{ $ns['date'] }} ({{ $ns['time'] }})</td>
                        <td class="text-center">
                            <span class="badge-pill {{ $ns['status_color'] === 'danger' ? 'badge-danger' : 'badge-gray' }}">
                                {{ $ns['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- 7. Lembar Pengesahan / Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td class="signature-col">
                <div>Dibuat Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Staf Administrasi Klinik</div>
                <div class="signature-space"></div>
                <div style="text-decoration: underline; font-weight: bold;">( Admin Operasional )</div>
                <div style="font-size: 9px; color: #64748b;">Klinik Gigi Senyum Sehat</div>
            </td>
            <td class="signature-col">
                <div>Mengetahui & Menyetujui,</div>
                <div style="font-weight: bold; margin-top: 2px;">Pimpinan / Dokter Penanggung Jawab</div>
                <div class="signature-space"></div>
                <div style="text-decoration: underline; font-weight: bold;">( drg. Budi Santoso, Sp.KG )</div>
                <div style="font-size: 9px; color: #64748b;">Penanggung Jawab Medis</div>
            </td>
        </tr>
    </table>

    <!-- 8. Footer Catatan -->
    <div class="footer-note">
        Dokumen ini dibuat otomatis oleh Sistem Informasi {{ $clinic->clinic_name ?? 'Klinik Gigi Senyum Sehat' }} pada {{ $printedAt }}.<br>
        Informasi dalam dokumen ini bersifat rahasia dan hanya untuk keperluan evaluasi internal klinik.
    </div>

</body>
</html>

