<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_minutes',
        'starting_price',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'starting_price' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, '/images/') || str_starts_with($this->image_path, 'images/')) {
            return url(ltrim($this->image_path, '/'));
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

