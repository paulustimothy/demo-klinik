<?php

namespace App\Filament\Pages;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Service;
use App\Services\ExportService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan Bulanan Reservasi & Performa';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.booking-report';

    public int $selectedMonth;

    public int $selectedYear;

    public ?array $filterData = [];

    public function mount(): void
    {
        $this->selectedMonth = (int) Carbon::now()->month;
        $this->selectedYear = (int) Carbon::now()->year;

        $this->filterData = [
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
        ];

        $this->form->fill($this->filterData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Periode Laporan')
                    ->description('Pilih bulan dan tahun untuk menganalisis performa reservasi & kehadiran pasien')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('selectedMonth')
                            ->label('Bulan')
                            ->options($this->getMonths())
                            ->default((int) Carbon::now()->month)
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedMonth = (int) $state;
                            }),

                        Forms\Components\Select::make('selectedYear')
                            ->label('Tahun')
                            ->options(array_combine($this->getYears(), $this->getYears()))
                            ->default((int) Carbon::now()->year)
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedYear = (int) $state;
                            }),
                    ])
                    ->columns(2),
            ])
            ->statePath('filterData');
    }

    public function getMonths(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function getYears(): array
    {
        $currentYear = (int) Carbon::now()->year;
        return range($currentYear - 2, $currentYear + 1);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(fn () => $this->exportPdf()),

            Action::make('exportExcel')
                ->label('Export Excel (.xlsx)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->exportExcel()),

            Action::make('exportCsv')
                ->label('Export CSV (.csv)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    /**
     * Hitung metrik laporan bulanan
     */
    public function getReportData(): array
    {
        $month = (int) ($this->filterData['selectedMonth'] ?? $this->selectedMonth ?? Carbon::now()->month);
        $year = (int) ($this->filterData['selectedYear'] ?? $this->selectedYear ?? Carbon::now()->year);

        $monthName = $this->getMonths()[$month] ?? 'Bulan ' . $month;

        // Ambil semua booking pada bulan & tahun terpilih
        $bookings = Booking::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with(['service', 'doctor'])
            ->get();

        $totalBookings = $bookings->count();
        $completed = $bookings->where('status', BookingStatus::COMPLETED)->count();
        $verified = $bookings->where('status', BookingStatus::VERIFIED)->count();
        $pending = $bookings->where('status', BookingStatus::PENDING)->count();
        $cancelled = $bookings->where('status', BookingStatus::CANCELLED)->count();
        $expired = $bookings->where('status', BookingStatus::EXPIRED)->count();
        $noShow = $bookings->where('status', BookingStatus::NO_SHOW)->count();

        // No-show rate: persentase pasien yang tidak hadir dari total booking
        $noShowRate = $totalBookings > 0 ? round(($noShow / $totalBookings) * 100, 1) : 0.0;

        // Attendance rate: kehadiran pasien (selesai / (selesai + no_show))
        $attendanceDenominator = $completed + $noShow;
        $attendanceRate = $attendanceDenominator > 0
            ? round(($completed / $attendanceDenominator) * 100, 1)
            : ($completed > 0 ? 100.0 : 0.0);

        // Estimasi nilai pendapatan dari kunjungan selesai
        $estimatedRevenue = 0;
        foreach ($bookings->where('status', BookingStatus::COMPLETED) as $b) {
            $estimatedRevenue += ($b->service?->starting_price ?? 0);
        }

        $summary = [
            'total_bookings' => $totalBookings,
            'completed' => $completed,
            'verified' => $verified,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'expired' => $expired,
            'no_show' => $noShow,
            'no_show_rate' => $noShowRate,
            'attendance_rate' => $attendanceRate,
            'estimated_revenue' => $estimatedRevenue,
        ];

        // 1. Rekapitulasi per Layanan
        $services = Service::orderBy('sort_order')->get();
        $serviceStats = [];
        foreach ($services as $srv) {
            $srvBookings = $bookings->where('service_id', $srv->id);
            $srvTotal = $srvBookings->count();
            $srvCompleted = $srvBookings->where('status', BookingStatus::COMPLETED)->count();
            $srvCancelledOrExpired = $srvBookings->whereIn('status', [BookingStatus::CANCELLED, BookingStatus::EXPIRED])->count();
            $srvNoShow = $srvBookings->where('status', BookingStatus::NO_SHOW)->count();
            $srvRevenue = $srvCompleted * ($srv->starting_price ?? 0);
            $srvShare = $totalBookings > 0 ? round(($srvTotal / $totalBookings) * 100, 1) : 0.0;

            $serviceStats[] = [
                'id' => $srv->id,
                'name' => $srv->name,
                'starting_price' => $srv->starting_price ?? 0,
                'total' => $srvTotal,
                'completed' => $srvCompleted,
                'cancelled_or_expired' => $srvCancelledOrExpired,
                'no_show' => $srvNoShow,
                'revenue' => $srvRevenue,
                'share_percentage' => $srvShare,
            ];
        }

        // 2. Rekapitulasi per Dokter
        $doctors = Doctor::all();
        $doctorStats = [];
        foreach ($doctors as $doc) {
            $docBookings = $bookings->where('doctor_id', $doc->id);
            $docTotal = $docBookings->count();
            $docCompleted = $docBookings->where('status', BookingStatus::COMPLETED)->count();
            $docCancelledOrExpired = $docBookings->whereIn('status', [BookingStatus::CANCELLED, BookingStatus::EXPIRED])->count();
            $docNoShow = $docBookings->where('status', BookingStatus::NO_SHOW)->count();

            $docResolved = $docCompleted + $docNoShow;
            $docAttendanceRate = $docResolved > 0
                ? round(($docCompleted / $docResolved) * 100, 1)
                : ($docCompleted > 0 ? 100.0 : 0.0);

            $doctorStats[] = [
                'id' => $doc->id,
                'name' => $doc->name,
                'title' => $doc->title,
                'total' => $docTotal,
                'completed' => $docCompleted,
                'cancelled_or_expired' => $docCancelledOrExpired,
                'no_show' => $docNoShow,
                'attendance_rate' => $docAttendanceRate,
            ];
        }

        // 3. Detail Pasien No-Show & Pembatalan Bulan Ini
        $noShowBookings = $bookings->whereIn('status', [
            BookingStatus::NO_SHOW,
            BookingStatus::CANCELLED,
            BookingStatus::EXPIRED,
        ])->sortByDesc('date');

        $noShowList = [];
        foreach ($noShowBookings as $item) {
            $noShowList[] = [
                'id' => $item->id,
                'code' => $item->code,
                'patient_name' => $item->patient_name,
                'patient_whatsapp' => $item->patient_whatsapp,
                'service_name' => $item->service?->name ?? '-',
                'doctor_name' => $item->doctor?->name ?? '-',
                'date' => Carbon::parse($item->date)->translatedFormat('d M Y'),
                'time' => substr($item->start_time, 0, 5) . ' WIB',
                'status' => $item->status->label(),
                'status_color' => $item->status->color(),
                'notes' => $item->notes,
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName,
            'summary' => $summary,
            'serviceStats' => $serviceStats,
            'doctorStats' => $doctorStats,
            'noShowList' => $noShowList,
        ];
    }

    /**
     * Dapatkan Stat Card KPI resmi Filament
     */
    public function getKpiStats(): array
    {
        $data = $this->getReportData();
        $summary = $data['summary'];

        return [
            Stat::make('Total Reservasi', $summary['total_bookings'])
                ->description("{$summary['pending']} Pending • {$summary['verified']} Terverifikasi")
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Kunjungan Selesai', $summary['completed'])
                ->description('Omset: Rp ' . number_format($summary['estimated_revenue'], 0, ',', '.'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Angka No-Show', $summary['no_show'])
                ->description("{$summary['no_show_rate']}% Rasio Tidak Hadir")
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($summary['no_show'] > 0 ? 'danger' : 'gray'),

            Stat::make('Tingkat Kehadiran', $summary['attendance_rate'] . '%')
                ->description("Batal: {$summary['cancelled']} • Expired: {$summary['expired']}")
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($summary['attendance_rate'] >= 80 ? 'success' : 'warning'),
        ];
    }

    public function exportExcel(): BinaryFileResponse
    {
        $data = $this->getReportData();
        return app(ExportService::class)->exportMonthlyReport(
            $data['summary'],
            $data['serviceStats'],
            $data['doctorStats'],
            $data['noShowList'],
            $data['monthName'],
            $data['year'],
            'xlsx'
        );
    }

    public function exportCsv(): BinaryFileResponse
    {
        $data = $this->getReportData();
        return app(ExportService::class)->exportMonthlyReport(
            $data['summary'],
            $data['serviceStats'],
            $data['doctorStats'],
            $data['noShowList'],
            $data['monthName'],
            $data['year'],
            'csv'
        );
    }

    public function exportPdf(): StreamedResponse
    {
        $data = $this->getReportData();
        return app(ExportService::class)->exportMonthlyPdf(
            $data['summary'],
            $data['serviceStats'],
            $data['doctorStats'],
            $data['noShowList'],
            $data['monthName'],
            $data['year']
        );
    }
}
