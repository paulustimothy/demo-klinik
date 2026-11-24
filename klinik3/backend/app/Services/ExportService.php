<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportService
{
    /**
     * Export data reservasi ke file Excel (.xlsx) atau CSV (.csv)
     *
     * @param iterable $records
     * @param string $format 'xlsx' atau 'csv'
     * @return BinaryFileResponse
     */
    public function exportBookings(iterable $records, string $format = 'xlsx'): BinaryFileResponse
    {
        $format = strtolower($format) === 'csv' ? 'csv' : 'xlsx';
        $timestamp = date('Ymd_His');
        $filename = "reservasi_klinik_{$timestamp}.{$format}";
        $tempPath = tempnam(sys_get_temp_dir(), 'export_bk_') . ".{$format}";

        $writer = $format === 'csv' ? new CsvWriter() : new XlsxWriter();
        $writer->openToFile($tempPath);

        // Header kolom
        $writer->addRow(Row::fromValues([
            'No',
            'Kode Booking',
            'Nama Pasien',
            'WhatsApp',
            'Layanan',
            'Dokter',
            'Tanggal',
            'Jam',
            'Status',
            'Catatan Pasien',
            'Waktu Dibuat',
        ]));

        $index = 1;
        foreach ($records as $record) {
            $serviceName = $record->service?->name ?? '-';
            $doctorName = $record->doctor?->name ?? '-';
            $date = $record->date ? Carbon::parse($record->date)->format('Y-m-d') : '-';
            $time = $record->start_time ? substr($record->start_time, 0, 5) . ' WIB' : '-';
            $status = method_exists($record->status, 'label') ? $record->status->label() : (string) $record->status;
            $createdAt = $record->created_at ? Carbon::parse($record->created_at)->format('Y-m-d H:i:s') : '-';

            $writer->addRow(Row::fromValues([
                $index++,
                $record->code,
                $record->patient_name,
                (string) $record->patient_whatsapp,
                $serviceName,
                $doctorName,
                $date,
                $time,
                $status,
                $record->notes ?: '-',
                $createdAt,
            ]));
        }

        $writer->close();

        $headers = [
            'Content-Type' => $format === 'csv'
                ? 'text/csv; charset=UTF-8'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return response()->download($tempPath, $filename, $headers)->deleteFileAfterSend(true);
    }

    /**
     * Export laporan bulanan ke file PDF yang rapi, menarik, dan dilengkapi grafik
     */
    public function exportMonthlyPdf(
        array $summary,
        array $serviceStats,
        array $doctorStats,
        array $noShowList,
        string $monthName,
        int $year
    ): \Symfony\Component\HttpFoundation\StreamedResponse {
        $timestamp = date('Ymd_His');
        $filename = "laporan_bulanan_{$monthName}_{$year}_{$timestamp}.pdf";
        $clinic = \App\Models\ClinicSetting::instance();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.monthly-booking-pdf', [
            'clinic' => $clinic,
            'summary' => $summary,
            'serviceStats' => $serviceStats,
            'doctorStats' => $doctorStats,
            'noShowList' => $noShowList,
            'monthName' => $monthName,
            'year' => $year,
            'printedAt' => now()->translatedFormat('d F Y H:i') . ' WIB',
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Export laporan bulanan lengkap (KPI, Layanan, Dokter, No-Show) ke Excel atau CSV
     */
    public function exportMonthlyReport(
        array $summary,
        array $serviceStats,
        array $doctorStats,
        array $noShowList,
        string $monthName,
        int $year,
        string $format = 'xlsx'
    ): BinaryFileResponse {
        $format = strtolower($format) === 'csv' ? 'csv' : 'xlsx';
        $timestamp = date('Ymd_His');
        $filename = "laporan_bulanan_{$monthName}_{$year}_{$timestamp}.{$format}";
        $tempPath = tempnam(sys_get_temp_dir(), 'export_rep_') . ".{$format}";

        $writer = $format === 'csv' ? new CsvWriter() : new XlsxWriter();
        $writer->openToFile($tempPath);

        if ($format === 'xlsx') {
            // === SHEET 1: RINGKASAN & LAYANAN ===
            $writer->getCurrentSheet()->setName('Ringkasan & Layanan');

            $writer->addRow(Row::fromValues(["LAPORAN BULANAN RESERVASI - KLINIK GIGI SENYUM SEHAT"]));
            $writer->addRow(Row::fromValues(["Periode:", "{$monthName} {$year}"]));
            $writer->addRow(Row::fromValues(["Waktu Ekspor:", date('d/m/Y H:i') . ' WIB']));
            $writer->addRow(Row::fromValues([]));

            // Ringkasan KPI
            $writer->addRow(Row::fromValues(['=== RINGKASAN INDIKATOR UTAMA (KPI) ===']));
            $writer->addRow(Row::fromValues(['Indikator', 'Jumlah / Nilai']));
            $writer->addRow(Row::fromValues(['Total Reservasi Masuk', $summary['total_bookings']]));
            $writer->addRow(Row::fromValues(['Reservasi Selesai (Hadir)', $summary['completed']]));
            $writer->addRow(Row::fromValues(['Terverifikasi (Aktif)', $summary['verified']]));
            $writer->addRow(Row::fromValues(['Menunggu Verifikasi', $summary['pending']]));
            $writer->addRow(Row::fromValues(['Dibatalkan (Cancelled)', $summary['cancelled']]));
            $writer->addRow(Row::fromValues(['Kedaluwarsa (Expired)', $summary['expired']]));
            $writer->addRow(Row::fromValues(['Tidak Hadir (No-Show)', $summary['no_show']]));
            $writer->addRow(Row::fromValues(['Angka / Rasio No-Show', $summary['no_show_rate'] . '%']));
            $writer->addRow(Row::fromValues(['Tingkat Kehadiran Pasien', $summary['attendance_rate'] . '%']));
            $writer->addRow(Row::fromValues(['Estimasi Nilai Layanan Selesai', 'Rp ' . number_format($summary['estimated_revenue'], 0, ',', '.')]));
            $writer->addRow(Row::fromValues([]));

            // Tabel Layanan
            $writer->addRow(Row::fromValues(['=== LAPORAN BOOKING PER LAYANAN ===']));
            $writer->addRow(Row::fromValues([
                'No',
                'Nama Layanan',
                'Tarif Dasar Mulai',
                'Total Booking',
                'Selesai',
                'Batal / Expired',
                'No-Show',
                'Estimasi Omset (Rp)',
                'Kontribusi (%)',
            ]));

            $idx = 1;
            foreach ($serviceStats as $stat) {
                $writer->addRow(Row::fromValues([
                    $idx++,
                    $stat['name'],
                    'Rp ' . number_format($stat['starting_price'], 0, ',', '.'),
                    $stat['total'],
                    $stat['completed'],
                    $stat['cancelled_or_expired'],
                    $stat['no_show'],
                    'Rp ' . number_format($stat['revenue'], 0, ',', '.'),
                    $stat['share_percentage'] . '%',
                ]));
            }

            // === SHEET 2: PER DOKTER ===
            $writer->addNewSheetAndMakeItCurrent()->setName('Per Dokter');
            $writer->addRow(Row::fromValues(["LAPORAN BOOKING PER DOKTER - {$monthName} {$year}"]));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues([
                'No',
                'Nama Dokter',
                'Spesialisasi / Gelar',
                'Total Pasien',
                'Selesai Dilayani',
                'Batal / Expired',
                'No-Show',
                'Tingkat Kehadiran (%)',
            ]));

            $idx = 1;
            foreach ($doctorStats as $dStat) {
                $writer->addRow(Row::fromValues([
                    $idx++,
                    $dStat['name'],
                    $dStat['title'],
                    $dStat['total'],
                    $dStat['completed'],
                    $dStat['cancelled_or_expired'],
                    $dStat['no_show'],
                    $dStat['attendance_rate'] . '%',
                ]));
            }

            // === SHEET 3: DETAIL NO-SHOW & PEMBATALAN ===
            $writer->addNewSheetAndMakeItCurrent()->setName('Pasien No-Show & Batal');
            $writer->addRow(Row::fromValues(["DAFTAR PASIEN NO-SHOW & PEMBATALAN - {$monthName} {$year}"]));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues([
                'No',
                'Kode Booking',
                'Nama Pasien',
                'No. WhatsApp',
                'Layanan',
                'Dokter',
                'Tanggal',
                'Jam',
                'Status',
                'Catatan',
            ]));

            $idx = 1;
            foreach ($noShowList as $item) {
                $writer->addRow(Row::fromValues([
                    $idx++,
                    $item['code'],
                    $item['patient_name'],
                    (string) $item['patient_whatsapp'],
                    $item['service_name'],
                    $item['doctor_name'],
                    $item['date'],
                    $item['time'],
                    $item['status'],
                    $item['notes'] ?: '-',
                ]));
            }
        } else {
            // === CSV FORMAT (SINGLE FILE) ===
            $writer->addRow(Row::fromValues(["LAPORAN BULANAN RESERVASI - KLINIK GIGI SENYUM SEHAT"]));
            $writer->addRow(Row::fromValues(["Periode:", "{$monthName} {$year}"]));
            $writer->addRow(Row::fromValues([]));

            // Summary
            $writer->addRow(Row::fromValues(['[RINGKASAN KPI]']));
            $writer->addRow(Row::fromValues(['Total Reservasi', $summary['total_bookings']]));
            $writer->addRow(Row::fromValues(['Selesai', $summary['completed']]));
            $writer->addRow(Row::fromValues(['Terverifikasi', $summary['verified']]));
            $writer->addRow(Row::fromValues(['Menunggu Verifikasi', $summary['pending']]));
            $writer->addRow(Row::fromValues(['Dibatalkan', $summary['cancelled']]));
            $writer->addRow(Row::fromValues(['Kedaluwarsa', $summary['expired']]));
            $writer->addRow(Row::fromValues(['Tidak Hadir (No-Show)', $summary['no_show']]));
            $writer->addRow(Row::fromValues(['Rasio No-Show', $summary['no_show_rate'] . '%']));
            $writer->addRow(Row::fromValues(['Tingkat Kehadiran', $summary['attendance_rate'] . '%']));
            $writer->addRow(Row::fromValues(['Estimasi Omset Selesai', 'Rp ' . number_format($summary['estimated_revenue'], 0, ',', '.')]));
            $writer->addRow(Row::fromValues([]));

            // Per Layanan
            $writer->addRow(Row::fromValues(['[BOOKING PER LAYANAN]']));
            $writer->addRow(Row::fromValues(['No', 'Layanan', 'Tarif Dasar', 'Total', 'Selesai', 'Batal/Expired', 'No-Show', 'Omset Estimasi', 'Kontribusi']));
            $idx = 1;
            foreach ($serviceStats as $stat) {
                $writer->addRow(Row::fromValues([
                    $idx++,
                    $stat['name'],
                    'Rp ' . number_format($stat['starting_price'], 0, ',', '.'),
                    $stat['total'],
                    $stat['completed'],
                    $stat['cancelled_or_expired'],
                    $stat['no_show'],
                    'Rp ' . number_format($stat['revenue'], 0, ',', '.'),
                    $stat['share_percentage'] . '%',
                ]));
            }
            $writer->addRow(Row::fromValues([]));

            // Per Dokter
            $writer->addRow(Row::fromValues(['[BOOKING PER DOKTER]']));
            $writer->addRow(Row::fromValues(['No', 'Dokter', 'Gelar', 'Total', 'Selesai', 'Batal/Expired', 'No-Show', 'Kehadiran']));
            $idx = 1;
            foreach ($doctorStats as $dStat) {
                $writer->addRow(Row::fromValues([
                    $idx++,
                    $dStat['name'],
                    $dStat['title'],
                    $dStat['total'],
                    $dStat['completed'],
                    $dStat['cancelled_or_expired'],
                    $dStat['no_show'],
                    $dStat['attendance_rate'] . '%',
                ]));
            }
            $writer->addRow(Row::fromValues([]));

            // No-Show list
            $writer->addRow(Row::fromValues(['[DAFTAR NO-SHOW & PEMBATALAN]']));
            $writer->addRow(Row::fromValues(['No', 'Kode', 'Pasien', 'WhatsApp', 'Layanan', 'Dokter', 'Tanggal', 'Jam', 'Status', 'Catatan']));
            $idx = 1;
            foreach ($noShowList as $item) {
                $writer->addRow(Row::fromValues([
                    $idx++,
                    $item['code'],
                    $item['patient_name'],
                    (string) $item['patient_whatsapp'],
                    $item['service_name'],
                    $item['doctor_name'],
                    $item['date'],
                    $item['time'],
                    $item['status'],
                    $item['notes'] ?: '-',
                ]));
            }
        }

        $writer->close();

        $headers = [
            'Content-Type' => $format === 'csv'
                ? 'text/csv; charset=UTF-8'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return response()->download($tempPath, $filename, $headers)->deleteFileAfterSend(true);
    }
}

