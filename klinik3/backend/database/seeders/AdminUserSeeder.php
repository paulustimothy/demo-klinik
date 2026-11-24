<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@kliniksenyumsehat.com');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        // 1. Akun Administrator (Akses Penuh)
        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin Senyum Sehat',
                'password' => Hash::make($adminPassword),
                'role' => UserRole::ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun Resepsionis (Operasional Reservasi & Pasien, tanpa akses ubah Dokter/Layanan/Pengaturan)
        User::updateOrCreate(
            ['email' => 'resepsionis@kliniksenyumsehat.com'],
            [
                'name' => 'Resepsionis Klinik',
                'password' => Hash::make('admin123'),
                'role' => UserRole::RECEPTIONIST,
                'email_verified_at' => now(),
            ]
        );
    }
}
