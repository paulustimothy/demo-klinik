<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'service_id',
        'doctor_id',
        'date',
        'start_time',
        'patient_name',
        'patient_whatsapp',
        'notes',
        'status',
        'slot_key',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'date' => 'date:Y-m-d',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Generate format slot key unik: {doctor_id}_{YYYY-MM-DD}_{HH:MM}
     */
    public static function generateSlotKey(int $doctorId, string $date, string $startTime): string
    {
        $normalizedTime = substr($startTime, 0, 5);
        return "{$doctorId}_{$date}_{$normalizedTime}";
    }

    /**
     * Generate kode booking unik: KG- diikuti 6 karakter alfanumerik kapital
     * tanpa karakter ambigu (O, 0, I, 1).
     */
    public static function generateUniqueCode(): string
    {
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charLength = strlen($characters);

        do {
            $code = 'KG-';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[random_int(0, $charLength - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Hitung sisa waktu hold dalam menit (hanya untuk status pending)
     */
    public function getRemainingHoldMinutesAttribute(): int
    {
        if ($this->status !== BookingStatus::PENDING || !$this->expires_at) {
            return 0;
        }

        $remaining = Carbon::now()->diffInMinutes($this->expires_at, false);
        return max(0, (int) $remaining);
    }

    /**
     * Verifikasi booking
     */
    public function verify(): bool
    {
        if ($this->status !== BookingStatus::PENDING) {
            return false;
        }

        $this->status = BookingStatus::VERIFIED;
        $this->verified_at = Carbon::now();
        $this->expires_at = null;
        return $this->save();
    }

    /**
     * Batalkan booking dan lepaskan slot
     */
    public function cancel(): bool
    {
        if (!in_array($this->status, [BookingStatus::PENDING, BookingStatus::VERIFIED])) {
            return false;
        }

        $this->status = BookingStatus::CANCELLED;
        $this->slot_key = null;
        return $this->save();
    }

    /**
     * Tandai booking selesai
     */
    public function complete(): bool
    {
        if ($this->status !== BookingStatus::VERIFIED) {
            return false;
        }

        $this->status = BookingStatus::COMPLETED;
        $this->slot_key = null;
        return $this->save();
    }

    /**
     * Tandai pasien tidak hadir (no-show) dan lepaskan slot
     */
    public function markNoShow(): bool
    {
        if ($this->status !== BookingStatus::VERIFIED) {
            return false;
        }

        $this->status = BookingStatus::NO_SHOW;
        $this->slot_key = null;
        return $this->save();
    }

    /**
     * Kedaluwarsa booking dan lepaskan slot
     */
    public function expire(): bool
    {
        if ($this->status !== BookingStatus::PENDING) {
            return false;
        }

        $this->status = BookingStatus::EXPIRED;
        $this->slot_key = null;
        return $this->save();
    }
}

