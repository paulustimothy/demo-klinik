<?php

namespace Database\Seeders;

use App\Models\ClinicSetting;
use Illuminate\Database\Seeder;

class ClinicSettingSeeder extends Seeder
{
    public function run(): void
    {
        ClinicSetting::truncate();

        ClinicSetting::create([
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

