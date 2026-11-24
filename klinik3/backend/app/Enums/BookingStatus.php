<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu verifikasi',
            self::VERIFIED => 'Terverifikasi',
            self::EXPIRED => 'Kedaluwarsa',
            self::CANCELLED => 'Dibatalkan',
            self::COMPLETED => 'Selesai',
            self::NO_SHOW => 'Tidak Hadir (No-Show)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::VERIFIED => 'success',
            self::EXPIRED => 'gray',
            self::CANCELLED => 'danger',
            self::COMPLETED => 'info',
            self::NO_SHOW => 'danger',
        };
    }

    public function holdsSlot(): bool
    {
        return in_array($this, [self::PENDING, self::VERIFIED]);
    }
}

