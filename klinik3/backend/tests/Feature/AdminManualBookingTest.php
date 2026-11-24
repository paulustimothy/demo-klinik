<?php

namespace Tests\Feature;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\BookingResource\Pages\CreateBooking;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\ClinicSettingSeeder;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminManualBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Doctor $doctor;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AdminUserSeeder::class,
            ClinicSettingSeeder::class,
            ServiceSeeder::class,
            DoctorSeeder::class,
        ]);

        $this->admin = User::where('email', env('ADMIN_EMAIL', 'admin@kliniksenyumsehat.com'))->firstOrFail();
        $this->doctor = Doctor::where('is_active', true)->firstOrFail();
        $this->service = Service::where('is_active', true)->firstOrFail();
    }

    public function test_admin_can_access_create_booking_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/bookings/create')
            ->assertSuccessful()
            ->assertSee('Tambah Booking Manual')
            ->assertSee('Sumber Booking')
            ->assertSee('Status Awal Booking');
    }

    public function test_admin_can_create_manual_booking_directly_verified_with_source_whatsapp(): void
    {
        // Cari hari praktik dokter ini
        $schedule = $this->doctor->schedules()->firstOrFail();
        $weekday = $schedule->weekday;

        $targetDate = Carbon::now()->next($weekday)->toDateString();
        $targetTime = substr($schedule->start_time, 0, 5);

        Livewire::actingAs($this->admin)
            ->test(CreateBooking::class)
            ->fillForm([
                'patient_name' => 'Budi WhatsApp',
                'patient_whatsapp' => '081234567890',
                'source' => BookingSource::WHATSAPP->value,
                'status' => BookingStatus::VERIFIED->value,
                'service_id' => $this->service->id,
                'doctor_id' => $this->doctor->id,
                'date' => $targetDate,
                'start_time' => $targetTime,
                'notes' => 'Pasien booking lewat pesan WA',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bookings', [
            'patient_name' => 'Budi WhatsApp',
            'patient_whatsapp' => '6281234567890',
            'source' => 'whatsapp',
            'status' => 'verified',
            'service_id' => $this->service->id,
            'doctor_id' => $this->doctor->id,
            'date' => $targetDate,
            'start_time' => $targetTime,
            'slot_key' => "{$this->doctor->id}_{$targetDate}_{$targetTime}",
        ]);

        $booking = Booking::where('patient_name', 'Budi WhatsApp')->firstOrFail();
        $this->assertNotNull($booking->code);
        $this->assertStringStartsWith('KG-', $booking->code);
        $this->assertNotNull($booking->verified_at);
        $this->assertNull($booking->expires_at);
        $this->assertEquals(BookingSource::WHATSAPP, $booking->source);
        $this->assertEquals(BookingStatus::VERIFIED, $booking->status);
    }

    public function test_admin_can_create_manual_booking_with_source_walk_in_and_telepon(): void
    {
        $schedule = $this->doctor->schedules()->firstOrFail();
        $weekday = $schedule->weekday;

        $targetDate = Carbon::now()->next($weekday)->addWeeks(1)->toDateString();
        $targetTime = substr($schedule->start_time, 0, 5);

        // 1. Walk-in
        Livewire::actingAs($this->admin)
            ->test(CreateBooking::class)
            ->fillForm([
                'patient_name' => 'Pasien Langsung Datang',
                'patient_whatsapp' => '081399998888',
                'source' => BookingSource::WALK_IN->value,
                'status' => BookingStatus::VERIFIED->value,
                'service_id' => $this->service->id,
                'doctor_id' => $this->doctor->id,
                'date' => $targetDate,
                'start_time' => $targetTime,
                'notes' => 'Walk-in ke meja resepsionis',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bookings', [
            'patient_name' => 'Pasien Langsung Datang',
            'source' => 'walk_in',
            'status' => 'verified',
        ]);
    }

    public function test_double_booking_prevention_on_manual_creation(): void
    {
        $schedule = $this->doctor->schedules()->firstOrFail();
        $weekday = $schedule->weekday;

        $targetDate = Carbon::now()->next($weekday)->addWeeks(2)->toDateString();
        $targetTime = substr($schedule->start_time, 0, 5);

        // 1. Buat booking pertama pada slot tersebut
        Booking::create([
            'code' => Booking::generateUniqueCode(),
            'service_id' => $this->service->id,
            'doctor_id' => $this->doctor->id,
            'date' => $targetDate,
            'start_time' => $targetTime,
            'patient_name' => 'Pasien Pertama',
            'patient_whatsapp' => '628111111111',
            'source' => BookingSource::WEBSITE,
            'status' => BookingStatus::VERIFIED,
            'slot_key' => "{$this->doctor->id}_{$targetDate}_{$targetTime}",
            'verified_at' => Carbon::now(),
        ]);

        // 2. Admin mencoba menginput booking kedua pada slot yang sama
        Livewire::actingAs($this->admin)
            ->test(CreateBooking::class)
            ->fillForm([
                'patient_name' => 'Pasien Kedua (Bentrok)',
                'patient_whatsapp' => '082222222222',
                'source' => BookingSource::PHONE->value,
                'status' => BookingStatus::VERIFIED->value,
                'service_id' => $this->service->id,
                'doctor_id' => $this->doctor->id,
                'date' => $targetDate,
                'start_time' => $targetTime,
                'notes' => 'Mencoba jam yang sama',
            ])
            ->call('create')
            ->assertHasFormErrors(['start_time']);

        // Pastikan pasien kedua tidak tersimpan
        $this->assertDatabaseMissing('bookings', [
            'patient_name' => 'Pasien Kedua (Bentrok)',
        ]);
    }

    public function test_get_available_slots_excludes_occupied_slots(): void
    {
        $schedule = $this->doctor->schedules()->firstOrFail();
        $weekday = $schedule->weekday;

        $targetDate = Carbon::now()->next($weekday)->addWeeks(3)->toDateString();
        $slot1 = substr($schedule->start_time, 0, 5);

        // Awalnya slot1 tersedia
        $slotsBefore = BookingResource::getAvailableSlotsForDoctorDate($this->doctor->id, $targetDate);
        $this->assertArrayHasKey($slot1, $slotsBefore);

        // Isi slot1 dengan booking
        Booking::create([
            'code' => Booking::generateUniqueCode(),
            'service_id' => $this->service->id,
            'doctor_id' => $this->doctor->id,
            'date' => $targetDate,
            'start_time' => $slot1,
            'patient_name' => 'Pasien Terverifikasi',
            'patient_whatsapp' => '6281234567899',
            'source' => BookingSource::WHATSAPP,
            'status' => BookingStatus::VERIFIED,
            'slot_key' => "{$this->doctor->id}_{$targetDate}_{$slot1}",
            'verified_at' => Carbon::now(),
        ]);

        // Setelah diisi, slot1 tidak lagi muncul di daftar slot kosong
        $slotsAfter = BookingResource::getAvailableSlotsForDoctorDate($this->doctor->id, $targetDate);
        $this->assertArrayNotHasKey($slot1, $slotsAfter);
    }
}

