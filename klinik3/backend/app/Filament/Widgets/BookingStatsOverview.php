<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek();

        $bookingHariIni = Booking::where('date', $today)->count();
        $menungguVerifikasi = Booking::where('status', BookingStatus::PENDING)->count();
        $terverifikasiMingguIni = Booking::where('status', BookingStatus::VERIFIED)
            ->where('verified_at', '>=', $startOfWeek)
            ->count();

        return [
            Stat::make('Booking hari ini', $bookingHariIni)
                ->description('Total reservasi terjadwal hari ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Menunggu verifikasi', $menungguVerifikasi)
                ->description('Perlu tindakan konfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($menungguVerifikasi > 0 ? 'warning' : 'gray'),

            Stat::make('Terverifikasi minggu ini', $terverifikasiMingguIni)
                ->description('Sejak awal pekan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}

