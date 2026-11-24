<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\ClinicSetting;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected static ?string $title = 'Tambah Booking Manual';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Generate unique code if not set
        if (empty($data['code'])) {
            $data['code'] = Booking::generateUniqueCode();
        }

        // 2. Normalisasi nomor WhatsApp pasien (format Indonesia 628...)
        $rawPhone = preg_replace('/[^\d]/', '', (string) ($data['patient_whatsapp'] ?? ''));
        if (str_starts_with($rawPhone, '08')) {
            $rawPhone = '62' . substr($rawPhone, 1);
        } elseif (str_starts_with($rawPhone, '8')) {
            $rawPhone = '62' . $rawPhone;
        }
        $data['patient_whatsapp'] = $rawPhone;

        // 3. Format start_time HH:MM
        $data['start_time'] = substr($data['start_time'], 0, 5);

        // 4. Generate slot_key unik
        $slotKey = Booking::generateSlotKey((int) $data['doctor_id'], $data['date'], $data['start_time']);
        $data['slot_key'] = $slotKey;

        // 5. Status & verifikasi: jika Terverifikasi, isi verified_at dan expires_at null
        $status = $data['status'] ?? BookingStatus::VERIFIED->value;
        if ($status instanceof BookingStatus) {
            $status = $status->value;
        }

        if ($status === BookingStatus::VERIFIED->value) {
            $data['status'] = BookingStatus::VERIFIED;
            $data['verified_at'] = Carbon::now();
            $data['expires_at'] = null;
        } else {
            $data['status'] = BookingStatus::PENDING;
            $holdMinutes = ClinicSetting::instance()->hold_minutes ?: 120;
            $data['expires_at'] = Carbon::now()->addMinutes($holdMinutes);
            $data['verified_at'] = null;
        }

        // 6. Double booking check awal
        $existing = Booking::where('slot_key', $slotKey)
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::VERIFIED])
            ->first();

        if ($existing) {
            if ($existing->status === BookingStatus::PENDING && $existing->expires_at && $existing->expires_at->isPast()) {
                $existing->expire();
            } else {
                throw ValidationException::withMessages([
                    'data.start_time' => 'Slot jam dan dokter pada tanggal tersebut sudah terisi oleh reservasi lain.',
                ]);
            }
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Pessimistic lock untuk mencegah race condition double booking
            $existing = Booking::where('slot_key', $data['slot_key'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status === BookingStatus::PENDING && $existing->expires_at && $existing->expires_at->isPast()) {
                    $existing->expire();
                } else {
                    throw ValidationException::withMessages([
                        'data.start_time' => 'Slot jam dan dokter pada tanggal tersebut baru saja dipesan oleh pasien lain.',
                    ]);
                }
            }

            return static::getModel()::create($data);
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Booking manual berhasil dibuat dan slot jadwal terkunci.';
    }
}
