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

class AvailabilityApiTest extends TestCase
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

    public function test_availability_returns_slots_for_practice_day(): void
    {
        // Dokter 1 (drg. Rina) praktik hari Senin(1), Rabu(3), Jumat(5) 09:00 - 19:00
        $doctor = Doctor::first();
        $nextMonday = Carbon::parse('next monday')->toDateString();

        $response = $this->getJson("/api/availability?doctor_id={$doctor->id}&date={$nextMonday}");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('09:00', $data[0]['time']);
        $this->assertTrue($data[0]['available']);
    }

    public function test_availability_returns_empty_array_on_sunday(): void
    {
        $doctor = Doctor::first();
        $nextSunday = Carbon::parse('next sunday')->toDateString();

        $response = $this->getJson("/api/availability?doctor_id={$doctor->id}&date={$nextSunday}");

        $response->assertStatus(200)
            ->assertExactJson([]);
    }

    public function test_availability_returns_empty_when_doctor_does_not_practice(): void
    {
        // Dokter 1 tidak praktik hari Selasa (2)
        $doctor = Doctor::first();
        $nextTuesday = Carbon::parse('next tuesday')->toDateString();

        $response = $this->getJson("/api/availability?doctor_id={$doctor->id}&date={$nextTuesday}");

        $response->assertStatus(200)
            ->assertExactJson([]);
    }

    public function test_booked_slot_is_marked_unavailable(): void
    {
        $doctor = Doctor::first();
        $service = Service::first();
        $nextMonday = Carbon::parse('next monday')->toDateString();

        // Buat pending booking pada jam 09:00
        Booking::create([
            'code' => 'KG-TEST01',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextMonday,
            'start_time' => '09:00',
            'patient_name' => 'Pasien Tes',
            'patient_whatsapp' => '6281234567899',
            'status' => BookingStatus::PENDING,
            'slot_key' => Booking::generateSlotKey($doctor->id, $nextMonday, '09:00'),
            'expires_at' => Carbon::now()->addHours(2),
        ]);

        $response = $this->getJson("/api/availability?doctor_id={$doctor->id}&date={$nextMonday}");

        $response->assertStatus(200);
        $slots = collect($response->json())->keyBy('time');
        $this->assertFalse($slots['09:00']['available']);
        $this->assertTrue($slots['10:00']['available']);
    }

    public function test_expired_pending_booking_releases_slot(): void
    {
        $doctor = Doctor::first();
        $service = Service::first();
        $nextMonday = Carbon::parse('next monday')->toDateString();

        // Pending booking yang expires_at di masa lalu
        Booking::create([
            'code' => 'KG-EXPIRE',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $nextMonday,
            'start_time' => '10:00',
            'patient_name' => 'Pasien Expired',
            'patient_whatsapp' => '6281234567899',
            'status' => BookingStatus::PENDING,
            'slot_key' => Booking::generateSlotKey($doctor->id, $nextMonday, '10:00'),
            'expires_at' => Carbon::now()->subMinutes(10),
        ]);

        $response = $this->getJson("/api/availability?doctor_id={$doctor->id}&date={$nextMonday}");

        $response->assertStatus(200);
        $slots = collect($response->json())->keyBy('time');
        // Slot tetap available karena hold sudah habis
        $this->assertTrue($slots['10:00']['available']);
    }
}

