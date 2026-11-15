<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_name',
        'tagline',
        'address',
        'hours_text',
        'whatsapp_number',
        'email',
        'hold_minutes',
        'slot_minutes',
    ];

    protected $casts = [
        'hold_minutes' => 'integer',
        'slot_minutes' => 'integer',
    ];

    public static function instance(): self
    {
        return static::first() ?? static::create([
            'clinic_name' => 'Klinik Gigi Senyum Sehat',
            'tagline' => 'Perawatan gigi lembut, nyaman, dan terpercaya untuk seluruh keluarga.',
            'address' => 'Jl. Contoh No. 12, Kota Contoh',
            'hours_text' => 'Senin – Sabtu 09.00 – 19.00, Minggu tutup',
            'whatsapp_number' => '6281234567890',
            'email' => 'halo@senyumsehat.example',
            'hold_minutes' => 120,
            'slot_minutes' => 60,
        ]);
    }
}

