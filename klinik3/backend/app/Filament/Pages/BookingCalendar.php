<?php

namespace App\Filament\Pages;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Doctor;
use Carbon\Carbon;
use Filament\Pages\Page;

class BookingCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Kalender Jadwal';

    protected static ?string $title = 'Kalender Jadwal & Reservasi Pasien';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.booking-calendar';

    public ?int $selectedDoctorId = null;

    public ?string $selectedStatus = null;

    public function getDoctorsProperty()
    {
        return Doctor::where('is_active', true)->orderBy('name')->get();
    }

    public function getEventsProperty(): array
    {
        $query = Booking::with(['doctor', 'service'])
            ->when($this->selectedDoctorId, fn ($q) => $q->where('doctor_id', $this->selectedDoctorId))
            ->when($this->selectedStatus, fn ($q) => $q->where('status', $this->selectedStatus));

        return $query->get()->map(function (Booking $booking) {
            $dateStr = $booking->date instanceof Carbon ? $booking->date->format('Y-m-d') : substr((string) $booking->date, 0, 10);
            $timeStr = substr($booking->start_time, 0, 5);
            $startDateTime = "{$dateStr}T{$timeStr}:00";

            $duration = $booking->service?->duration_minutes ?: 60;
            $endDateTime = Carbon::parse("{$dateStr} {$timeStr}")->addMinutes($duration)->toIso8601String();

            $color = match ($booking->status) {
                BookingStatus::PENDING => '#f59e0b',   // amber-500
                BookingStatus::VERIFIED => '#10b981',  // emerald-500
                BookingStatus::COMPLETED => '#3b82f6', // blue-500
                BookingStatus::CANCELLED => '#ef4444', // red-500
                BookingStatus::EXPIRED => '#6b7280',   // gray-500
                BookingStatus::NO_SHOW => '#b91c1c',   // red-700
            };

            // Nama dokter ringkas untuk label kalender
            $docName = $booking->doctor?->name ?? 'Dokter';
            $docParts = explode(' ', $docName);
            $docShort = count($docParts) >= 2 ? ($docParts[0] . ' ' . $docParts[1]) : $docName;

            return [
                'id' => (string) $booking->id,
                'title' => "{$timeStr} {$booking->patient_name} ({$docShort})",
                'start' => $startDateTime,
                'end' => $endDateTime,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'code' => $booking->code,
                    'patient_name' => $booking->patient_name,
                    'patient_whatsapp' => $booking->patient_whatsapp,
                    'doctor_name' => $booking->doctor?->name ?? '-',
                    'doctor_short' => $docShort,
                    'service_name' => $booking->service?->name ?? '-',
                    'status' => $booking->status->label(),
                    'status_value' => $booking->status->value,
                    'status_color' => $color,
                    'source' => $booking->source?->label() ?? 'Website',
                    'date' => Carbon::parse($booking->date)->translatedFormat('d F Y'),
                    'time' => "{$timeStr} WIB",
                    'notes' => $booking->notes ?: '-',
                ],
            ];
        })->values()->toArray();
    }
}
