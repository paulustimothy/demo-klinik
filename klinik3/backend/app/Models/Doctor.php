<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'years_experience',
        'photo_path',
        'is_active',
    ];

    protected $casts = [
        'years_experience' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }

        if (str_starts_with($this->photo_path, '/images/') || str_starts_with($this->photo_path, 'images/')) {
            return url(ltrim($this->photo_path, '/'));
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->photo_path);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

