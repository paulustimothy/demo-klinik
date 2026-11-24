<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'whatsapp',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->orderBy('date', 'desc')->orderBy('start_time', 'desc');
    }

    public function getLastVisitAttribute(): ?string
    {
        $lastBooking = $this->bookings()->where('date', '<=', Carbon::today())->first();
        if (!$lastBooking) {
            $lastBooking = $this->bookings()->first();
        }

        return $lastBooking ? Carbon::parse($lastBooking->date)->translatedFormat('d M Y') : '-';
    }

    public function getTotalVisitsAttribute(): int
    {
        return $this->bookings()->count();
    }
}

