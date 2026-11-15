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

class BookingSchedulerTest extends TestCase
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

    public function test_expire_command_expires_overdue_pending_bookings(): void
    {
        $doctor = Doctor::first();
        $service = Service::first();
        $date = Carbon::parse('next monday')->toDateString();

        // 1. Booking pending yang sudah melewati batas hold
        $overdueBooking = Booking::create([
            'code' => 'KG-EXPIRED1',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $date,
            'start_time' => '09:00',
            'patient_name' => 'Pasien Overdue',
            'patient_whatsapp' => '6281234567891',
            'status' => BookingStatus::PENDING,
            'slot_key' => Booking::generateSlotKey($doctor->id, $date, '09:00'),
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        // 2. Booking pending yang masih aktif dalam batas hold
        $activeBooking = Booking::create([
            'code' => 'KG-ACTIVE1',
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => $date,
            'start_time' => '10:00',
            'patient_name' => 'Pasien Aktif',
            'patient_whatsapp' => '6281234567892',
            'status' => BookingStatus::PENDING,
            'slot_key' => Booking::generateSlotKey($doctor->id, $date, '10:00'),
            'expires_at' => Carbon::now()->addHour(),
        ]);

        $this->artisan('bookings:expire')
            ->expectsOutputToContain('Berhasil kedaluwarsa 1 booking')
            ->assertExitCode(0);

        $overdueBooking->refresh();
        $this->assertEquals(BookingStatus::EXPIRED, $overdueBooking->status);
        $this->assertNull($overdueBooking->slot_key);

        $activeBooking->refresh();
        $this->assertEquals(BookingStatus::PENDING, $activeBooking->status);
        $this->assertNotNull($activeBooking->slot_key);
    }
}

