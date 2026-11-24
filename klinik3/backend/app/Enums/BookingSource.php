<?php

namespace App\Enums;

enum BookingSource: string
{
    case WEBSITE = 'website';
    case WHATSAPP = 'whatsapp';
    case PHONE = 'phone';
    case WALK_IN = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::WEBSITE => 'Website',
            self::WHATSAPP => 'WhatsApp',
            self::PHONE => 'Telepon',
            self::WALK_IN => 'Walk-in',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WEBSITE => 'info',
            self::WHATSAPP => 'success',
            self::PHONE => 'warning',
            self::WALK_IN => 'primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WEBSITE => 'heroicon-m-globe-alt',
            self::WHATSAPP => 'heroicon-m-chat-bubble-left-ellipsis',
            self::PHONE => 'heroicon-m-phone',
            self::WALK_IN => 'heroicon-m-user',
        };
    }
}

