<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Service;
use Carbon\Carbon;
use Database\Seeders\ClinicSettingSeeder;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            ClinicSettingSeeder::class,
            ServiceSeeder::class,
            DoctorSeeder::class,
        ]);
    }

    public function test_can_create_pending_booking_successfully(): void
    {
        $doctor = Doctor::first(); // drg. Rina (Senin, Rabu, Jumat)
        $service = Service::first();
        $nextMonday = Carbon::parse('next monday')->toDateString();

        $payload = [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextMonday,
            'time' => '10:00',
            'name' => 'Budi Pratama',
            'whatsapp' => '081234567890',
            'notes' => 'Pembersihan karang gigi pertama kali',
        ];

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'code',
                'status',
                'expires_at',
                'booking' => [
                    'service_name',
                    'doctor_name',
                    'date',
                    'time',
                    'patient_name',
                ],
            ])
            ->assertJson([
                'status' => 'Menunggu verifikasi',
                'booking' => [
                    'service_name' => $service->name,
                    'doctor_name' => $doctor->name,
                    'date' => $nextMonday,
                    'time' => '10:00',
                    'patient_name' => 'Budi Pratama',
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'date' => $nextMonday,
            'start_time' => '10:00',
            'patient_whatsapp' => '6281234567890',
            'status' => 'pending',
            'slot_key' => "{$doctor->id}_{$nextMonday}_10:00",
        ]);
    }

    public function test_booking_validation_fails_with_indonesian_messages(): void
    {
        $response = $this->postJson('/api/bookings', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['service_id', 'doctor_id', 'date', 'time', 'name', 'whatsapp'],
            ]);

        $this->assertStringContainsString('wajib', $response->json('errors.name.0'));
    }

    public function test_booking_fails_on_sunday(): void
    {
        $doctor = Doctor::first();
        $service = Service::first();
        $nextSunday = Carbon::parse('next sunday')->toDateString();

        $response = $this->postJson('/api/bookings', [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextSunday,
            'time' => '10:00',
            'name' => 'Pasien Minggu',
            'whatsapp' => '081234567890',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'date' => ['Klinik tutup pada hari Minggu.'],
            ]);
    }

    public function test_booking_fails_when_doctor_does_not_practice(): void
    {
        $doctor = Doctor::first(); // drg. Rina tidak praktik hari Selasa
        $service = Service::first();
        $nextTuesday = Carbon::parse('next tuesday')->toDateString();

        $response = $this->postJson('/api/bookings', [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextTuesday,
            'time' => '10:00',
            'name' => 'Pasien Selasa',
            'whatsapp' => '081234567890',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'date' => ['Dokter tidak berpraktik pada hari yang dipilih.'],
            ]);
    }

    public function test_double_booking_returns_409(): void
    {
        $doctor = Doctor::first();
        $service = Service::first();
        $nextMonday = Carbon::parse('next monday')->toDateString();

        $payload = [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextMonday,
            'time' => '11:00',
            'name' => 'Pasien Pertama',
            'whatsapp' => '081234567891',
        ];

        // Reservasi pertama sukses
        $res1 = $this->postJson('/api/bookings', $payload);
        $res1->assertStatus(201);

        // Reservasi kedua untuk dokter, tanggal, dan jam yang sama harus 409
        $payload2 = [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextMonday,
            'time' => '11:00',
            'name' => 'Pasien Kedua',
            'whatsapp' => '081234567892',
        ];

        $res2 = $this->postJson('/api/bookings', $payload2);
        $res2->assertStatus(409)
            ->assertJson([
                'message' => 'Jam ini baru saja dipesan, silakan pilih jam lain.',
            ]);
    }

    public function test_max_two_pending_bookings_per_whatsapp(): void
    {
        $doctor = Doctor::first();
        $service = Service::first();
        $nextMonday = Carbon::parse('next monday')->toDateString();
        $nextWednesday = Carbon::parse('next wednesday')->toDateString();
        $nextFriday = Carbon::parse('next friday')->toDateString();

        $whatsapp = '081234567893';

        // Booking 1 (sukses)
        $this->postJson('/api/bookings', [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextMonday,
            'time' => '09:00',
            'name' => 'Pasien Sama',
            'whatsapp' => $whatsapp,
        ])->assertStatus(201);

        // Booking 2 (sukses)
        $this->postJson('/api/bookings', [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextWednesday,
            'time' => '09:00',
            'name' => 'Pasien Sama',
            'whatsapp' => $whatsapp,
        ])->assertStatus(201);

        // Booking 3 (harus ditolak 422)
        $res3 = $this->postJson('/api/bookings', [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextFriday,
            'time' => '09:00',
            'name' => 'Pasien Sama',
            'whatsapp' => $whatsapp,
        ]);

        $res3->assertStatus(422);
        $this->assertStringContainsString('sudah memiliki 2 booking aktif', $res3->json('message'));
    }

    public function test_rate_limit_exceeded_returns_429(): void
    {
        $doctor = Doctor::first();
        $service = Service::first();
        $nextMonday = Carbon::parse('next monday')->toDateString();

        $times = ['09:00', '10:00', '11:00', '13:00', '14:00'];

        foreach ($times as $index => $time) {
            $res = $this->postJson('/api/bookings', [
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'date' => $nextMonday,
                'time' => $time,
                'name' => "Pasien {$index}",
                'whatsapp' => "08129999000{$index}",
            ]);
            $res->assertStatus(201);
        }

        // Request ke-6 dalam 1 jam harus 429
        $res6 = $this->postJson('/api/bookings', [
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextMonday,
            'time' => '15:00',
            'name' => 'Pasien Keenam',
            'whatsapp' => '081299990009',
        ]);

        $res6->assertStatus(429);
    }
}

