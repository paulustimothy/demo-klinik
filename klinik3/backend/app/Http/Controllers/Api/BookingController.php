<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $normalizedPhone = $request->input('normalized_whatsapp');

        // Batasi maksimal 2 pending booking aktif per nomor WhatsApp
        $activePendingCount = Booking::where('patient_whatsapp', $normalizedPhone)
            ->where('status', BookingStatus::PENDING)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->count();

        if ($activePendingCount >= 2) {
            return response()->json([
                'message' => 'Nomor WhatsApp ini sudah memiliki 2 booking aktif yang menunggu verifikasi.',
                'errors' => [
                    'whatsapp' => ['Nomor WhatsApp ini sudah memiliki 2 booking aktif yang menunggu verifikasi.'],
                ],
            ], 422);
        }

        $service = Service::find($validated['service_id']);
        $doctor = Doctor::find($validated['doctor_id']);
        $settings = ClinicSetting::instance();
        $holdMinutes = $settings->hold_minutes ?: 120;

        $slotKey = Booking::generateSlotKey(
            $doctor->id,
            $validated['date'],
            $validated['time']
        );

        try {
            $booking = DB::transaction(function () use ($validated, $normalizedPhone, $doctor, $service, $slotKey, $holdMinutes) {
                // Cek apakah slot sudah terisi (double-booking check dengan pesimistic lock)
                $existingActive = Booking::where('slot_key', $slotKey)
                    ->lockForUpdate()
                    ->first();

                if ($existingActive) {
                    // Jika pending namun waktu sudah hangus, kita bisa expire slot lama
                    if ($existingActive->status === BookingStatus::PENDING && $existingActive->expires_at && $existingActive->expires_at->isPast()) {
                        $existingActive->expire();
                    } else {
                        return null;
                    }
                }

                return Booking::create([
                    'code' => Booking::generateUniqueCode(),
                    'service_id' => $service->id,
                    'doctor_id' => $doctor->id,
                    'date' => $validated['date'],
                    'start_time' => $validated['time'],
                    'patient_name' => $validated['name'],
                    'patient_whatsapp' => $normalizedPhone,
                    'source' => BookingSource::WEBSITE,
                    'notes' => $validated['notes'] ?? null,
                    'status' => BookingStatus::PENDING,
                    'slot_key' => $slotKey,
                    'expires_at' => Carbon::now()->addMinutes($holdMinutes),
                    'verified_at' => null,
                ]);
            });

            if (!$booking) {
                return response()->json([
                    'message' => 'Jam ini baru saja dipesan, silakan pilih jam lain.',
                ], 409);
            }

            return response()->json([
                'code' => $booking->code,
                'status' => $booking->status->label(),
                'expires_at' => $booking->expires_at->toIso8601String(),
                'booking' => [
                    'service_name' => $service->name,
                    'doctor_name' => $doctor->name,
                    'date' => $booking->date->format('Y-m-d'),
                    'time' => $booking->start_time,
                    'patient_name' => $booking->patient_name,
                ],
            ], 201);
        } catch (QueryException $e) {
            // Tangkap MySQL unique constraint violation pada slot_key (kode error 23000 / 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'slot_key')) {
                return response()->json([
                    'message' => 'Jam ini baru saja dipesan, silakan pilih jam lain.',
                ], 409);
            }
            throw $e;
        }
    }
}

