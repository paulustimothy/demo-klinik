<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DoctorSchedule::truncate();
        Doctor::truncate();
        Schema::enableForeignKeyConstraints();

        $doctors = [
            [
                'name' => 'drg. Rina Pratama',
                'title' => 'Dokter Gigi Umum',
                'years_experience' => 8,
                'photo_path' => '/images/doctor-rina.webp',
                'is_active' => true,
                'schedules' => [
                    ['weekday' => 1, 'start_time' => '09:00', 'end_time' => '19:00'],
                    ['weekday' => 3, 'start_time' => '09:00', 'end_time' => '19:00'],
                    ['weekday' => 5, 'start_time' => '09:00', 'end_time' => '19:00'],
                ],
            ],
            [
                'name' => 'drg. Budi Santoso',
                'title' => 'Dokter Gigi Umum',
                'years_experience' => 12,
                'photo_path' => '/images/doctor-budi.webp',
                'is_active' => true,
                'schedules' => [
                    ['weekday' => 2, 'start_time' => '09:00', 'end_time' => '19:00'],
                    ['weekday' => 4, 'start_time' => '09:00', 'end_time' => '19:00'],
                    ['weekday' => 6, 'start_time' => '09:00', 'end_time' => '19:00'],
                ],
            ],
            [
                'name' => 'drg. Maya Lestari',
                'title' => 'Dokter Gigi Umum',
                'years_experience' => 5,
                'photo_path' => '/images/doctor-maya.webp',
                'is_active' => true,
                'schedules' => [
                    ['weekday' => 1, 'start_time' => '09:00', 'end_time' => '19:00'],
                    ['weekday' => 4, 'start_time' => '09:00', 'end_time' => '19:00'],
                    ['weekday' => 6, 'start_time' => '09:00', 'end_time' => '19:00'],
                ],
            ],
        ];

        foreach ($doctors as $data) {
            $schedules = $data['schedules'];
            unset($data['schedules']);

            $doctor = Doctor::create($data);

            foreach ($schedules as $schedule) {
                $doctor->schedules()->create($schedule);
            }
        }
    }
}

