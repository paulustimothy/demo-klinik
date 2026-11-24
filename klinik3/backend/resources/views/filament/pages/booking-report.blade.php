<x-filament-panels::page>
    <style>
        .no-truncate .truncate,
        .no-truncate [class*="truncate"],
        .fi-badge-full .truncate,
        .fi-badge-full [class*="truncate"] {
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: nowrap !important;
        }
    </style>

    @php
        $data = $this->getReportData();
        $summary = $data['summary'];
        $serviceStats = $data['serviceStats'];
        $doctorStats = $data['doctorStats'];
        $noShowList = $data['noShowList'];
        $monthName = $data['monthName'];
        $year = $data['year'];
    @endphp

    <div class="space-y-6">
        <!-- 1. Filter Periode (Menggunakan Filament Form Native) -->
        <div>
            {{ $this->form }}
        </div>

        <!-- 2. KPI Cards (Native Filament Stat Cards dalam Grid Responsif) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;" class="fi-wi-stats-overview-stats-ctn">
            @foreach ($this->getKpiStats() as $stat)
                {{ $stat }}
            @endforeach
        </div>

        <!-- 3. Tabel Laporan Bulanan Booking per Layanan -->
        <x-filament::section
            icon="heroicon-o-wrench-screwdriver"
            collapsible
        >
            <x-slot name="heading">
                Laporan Booking per Layanan ({{ $monthName }} {{ $year }})
            </x-slot>
            <x-slot name="description">
                Distribusi reservasi, penyelesaian, pembatalan, dan no-show berdasarkan layanan
            </x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="info">
                    {{ count($serviceStats) }} Layanan Terdaftar
                </x-filament::badge>
            </x-slot>

            <div class="overflow-x-auto -mx-6 -mb-6">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead>
                        <tr class="bg-gray-50/75 dark:bg-white/5">
                            <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Layanan</th>
                            <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Tarif Mulai</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Total Booking</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Selesai</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Batal / Expired</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">No-Show</th>
                            <th class="px-6 py-3.5 text-end text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Estimasi Omset</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                        @forelse ($serviceStats as $srv)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-950 dark:text-white text-sm">
                                    {{ $srv['name'] }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs font-medium text-gray-700 dark:text-white">
                                    Rp {{ number_format($srv['starting_price'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-950 dark:text-white text-sm">
                                    {{ $srv['total'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-filament::badge color="success">
                                        {{ $srv['completed'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-filament::badge color="gray">
                                        {{ $srv['cancelled_or_expired'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($srv['no_show'] > 0)
                                        <x-filament::badge color="danger">
                                            {{ $srv['no_show'] }}
                                        </x-filament::badge>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-bold text-gray-950 dark:text-white text-sm">
                                    Rp {{ number_format($srv['revenue'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2.5">
                                        <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                            <div class="bg-primary-600 dark:bg-primary-500 h-2 rounded-full" style="width: {{ $srv['share_percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $srv['share_percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada data reservasi untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- 4. Tabel Laporan Bulanan Booking per Dokter -->
        <x-filament::section
            icon="heroicon-o-user-group"
            collapsible
        >
            <x-slot name="heading">
                Laporan Booking per Dokter ({{ $monthName }} {{ $year }})
            </x-slot>
            <x-slot name="description">
                Analisis jadwal pasien, penyelesaian konsultasi, dan angka no-show per dokter
            </x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="info">
                    {{ count($doctorStats) }} Dokter Praktek
                </x-filament::badge>
            </x-slot>

            <div class="overflow-x-auto -mx-6 -mb-6">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead>
                        <tr class="bg-gray-50/75 dark:bg-white/5">
                            <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Nama Dokter</th>
                            <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Spesialisasi / Gelar</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Total Pasien</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Selesai Dilayani</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Batal / Expired</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">No-Show</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Tingkat Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                        @forelse ($doctorStats as $doc)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-950 dark:text-white text-sm">
                                    {{ $doc['name'] }}
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-600 dark:text-white">
                                    {{ $doc['title'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-950 dark:text-white text-sm">
                                    {{ $doc['total'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-filament::badge color="success">
                                        {{ $doc['completed'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-filament::badge color="gray">
                                        {{ $doc['cancelled_or_expired'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($doc['no_show'] > 0)
                                        <x-filament::badge color="danger">
                                            {{ $doc['no_show'] }}
                                        </x-filament::badge>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-filament::badge :color="$doc['attendance_rate'] >= 80 ? 'success' : ($doc['attendance_rate'] >= 50 ? 'warning' : 'danger')">
                                            {{ $doc['attendance_rate'] }}%
                                        </x-filament::badge>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada data dokter untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- 5. Daftar Pasien No-Show & Pembatalan -->
        @if (count($noShowList) > 0)
            <x-filament::section
                icon="heroicon-o-exclamation-triangle"
                collapsible
            >
                <x-slot name="heading">
                    Daftar Pasien Tidak Hadir (No-Show) & Dibatalkan
                </x-slot>
                <x-slot name="description">
                    Daftar pasien yang tidak hadir atau membatalkan reservasi pada {{ $monthName }} {{ $year }}
                </x-slot>
                <x-slot name="headerEnd">
                    <x-filament::badge color="danger">
                        {{ count($noShowList) }} Pasien
                    </x-filament::badge>
                </x-slot>

                <div class="overflow-x-auto -mx-6 -mb-6">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead>
                            <tr class="bg-gray-50/75 dark:bg-white/5">
                                <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">Kode</th>
                                <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">Nama Pasien</th>
                                <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">WhatsApp</th>
                                <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">Layanan</th>
                                <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">Dokter</th>
                                <th class="px-6 py-3.5 text-start text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">Jadwal</th>
                                <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap" style="min-width: 180px; width: 180px;">Status</th>
                                <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap" style="min-width: 140px; width: 140px;">Aksi Hubungi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                            @foreach ($noShowList as $ns)
                                @php
                                    $phone = preg_replace('/[^\d]/', '', (string) $ns['patient_whatsapp']);
                                    if (str_starts_with($phone, '08')) {
                                        $phone = '62' . substr($phone, 1);
                                    }
                                    $waText = urlencode("Halo {$ns['patient_name']}, kami dari Klinik Gigi Senyum Sehat. Kami mencatat reservasi Anda pada {$ns['date']} jam {$ns['time']} belum sempat hadir. Apakah ada kendala atau ingin menjadwalkan ulang?");
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-xs text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                        {{ $ns['code'] }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-950 dark:text-white text-sm whitespace-nowrap">
                                        {{ $ns['patient_name'] }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs font-medium text-gray-700 dark:text-white whitespace-nowrap">
                                        {{ $ns['patient_whatsapp'] }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-gray-700 dark:text-white whitespace-nowrap">
                                        {{ $ns['service_name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-gray-700 dark:text-white whitespace-nowrap">
                                        {{ $ns['doctor_name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ $ns['date'] }} ({{ $ns['time'] }})
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap no-truncate" style="min-width: 180px;">
                                        <x-filament::badge :color="$ns['status_color']" class="fi-badge-full inline-flex" style="white-space: nowrap !important; min-width: max-content;">
                                            {{ $ns['status'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap" style="min-width: 140px;">
                                        <x-filament::button
                                            tag="a"
                                            :href="'https://wa.me/' . $phone . '?text=' . $waText"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            size="xs"
                                            color="success"
                                            icon="heroicon-m-chat-bubble-left-ellipsis"
                                            style="white-space: nowrap;"
                                        >
                                            Follow-up WA
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
