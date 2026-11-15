<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ], [
            'doctor_id.required' => 'Dokter wajib dipilih.',
            'doctor_id.exists' => 'Dokter tidak ditemukan.',
            'date.required' => 'Tanggal reservasi wajib diisi.',
            'date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $doctorId = (int) $request->query('doctor_id');
        $dateStr = $request->query('date');
        $date = Carbon::parse($dateStr);

        // Klinik tutup hari Minggu (ISO weekday 7)
        if ($date->dayOfWeekIso === 7) {
            return response()->json([]);
        }

        $doctor = Doctor::where('is_active', true)->find($doctorId);
        if (!$doctor) {
            return response()->json([
                'message' => 'Dokter tidak aktif atau tidak ditemukan.',
            ], 404);
        }

        $weekday = $date->dayOfWeekIso; // 1 = Monday ... 6 = Saturday
        $schedule = $doctor->schedules()->where('weekday', $weekday)->first();
        if (!$schedule) {
            return response()->json([]);
        }

        $settings = ClinicSetting::instance();
        $slotMinutes = $settings->slot_minutes ?: 60;

        $startTime = Carbon::createFromTimeString($schedule->start_time);
        $endTime = Carbon::createFromTimeString($schedule->end_time);

        // Ambil booking aktif untuk dokter & tanggal ini
        $activeBookings = Booking::where('doctor_id', $doctorId)
            ->where('date', $dateStr)
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::VERIFIED])
            ->get()
            ->filter(function ($booking) {
                // Jika pending tetapi sudah expired, anggap tidak aktif
                if ($booking->status === BookingStatus::PENDING && $booking->expires_at && $booking->expires_at->isPast()) {
                    return false;
                }
                return true;
            })
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        $slots = [];
        $current = $startTime->copy();
        $now = Carbon::now();
        $isToday = $date->isToday();

        while ($current->copy()->addMinutes($slotMinutes)->lte($endTime)) {
            $timeStr = $current->format('H:i');

            // Cek apakah slot sudah diambil
            $isBooked = in_array($timeStr, $activeBookings, true);

            // Cek apakah slot sudah lewat untuk hari ini
            $isPast = false;
            if ($isToday) {
                $slotDateTime = Carbon::parse("{$dateStr} {$timeStr}");
                if ($slotDateTime->lte($now)) {
                    $isPast = true;
                }
            }

            $available = !$isBooked && !$isPast;

            $slots[] = [
                'time' => $timeStr,
                'available' => $available,
            ];

            $current->addMinutes($slotMinutes);
        }

        return response()->json($slots);
    }
}

