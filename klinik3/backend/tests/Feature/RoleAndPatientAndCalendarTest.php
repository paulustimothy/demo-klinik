<?php

namespace Tests\Feature;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Filament\Pages\BookingCalendar;
use App\Filament\Resources\PatientResource;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\BookingSeeder;
use Database\Seeders\ClinicSettingSeeder;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleAndPatientAndCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AdminUserSeeder::class,
            ClinicSettingSeeder::class,
            ServiceSeeder::class,
            DoctorSeeder::class,
            BookingSeeder::class,
        ]);

        $this->admin = User::where('role', UserRole::ADMIN)->firstOrFail();
        $this->receptionist = User::where('role', UserRole::RECEPTIONIST)->firstOrFail();
    }

    public function test_admin_has_full_access(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/manage-clinic-settings')
            ->assertSuccessful();

        $this->actingAs($this->admin)
            ->get('/admin/doctors/create')
            ->assertSuccessful();

        $this->actingAs($this->admin)
            ->get('/admin/services/create')
            ->assertSuccessful();
    }

    public function test_receptionist_cannot_modify_doctors_services_or_settings(): void
    {
        // 1. Resepsionis tidak bisa mengakses Pengaturan Klinik
        $this->actingAs($this->receptionist)
            ->get('/admin/manage-clinic-settings')
            ->assertForbidden();

        // 2. Resepsionis tidak bisa mengakses create dokter
        $this->actingAs($this->receptionist)
            ->get('/admin/doctors/create')
            ->assertForbidden();

        // 3. Resepsionis tidak bisa mengakses create layanan
        $this->actingAs($this->receptionist)
            ->get('/admin/services/create')
            ->assertForbidden();

        // 4. Namun resepsionis tetap bisa melihat daftar dokter, layanan, reservasi, kalender, dan pasien
        $this->actingAs($this->receptionist)
            ->get('/admin/doctors')
            ->assertSuccessful();

        $this->actingAs($this->receptionist)
            ->get('/admin/services')
            ->assertSuccessful();

        $this->actingAs($this->receptionist)
            ->get('/admin/bookings')
            ->assertSuccessful();

        $this->actingAs($this->receptionist)
            ->get('/admin/patients')
            ->assertSuccessful();

        $this->actingAs($this->receptionist)
            ->get('/admin/booking-calendar')
            ->assertSuccessful();
    }

    public function test_patient_matching_respects_name_and_does_not_merge_different_names_with_same_phone(): void
    {
        $service = Service::firstOrFail();
        $doctor = Doctor::firstOrFail();
        $sharedPhone = '628999888777';

        // 1. Booking pertama untuk Ibu
        $bookingIbu = Booking::create([
            'code' => Booking::generateUniqueCode(),
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00',
            'patient_name' => 'Ibu Ratna',
            'patient_whatsapp' => $sharedPhone,
            'source' => BookingSource::WHATSAPP,
            'status' => BookingStatus::VERIFIED,
            'slot_key' => "{$doctor->id}_" . now()->toDateString() . "_10:00",
        ]);

        // 2. Booking kedua menggunakan nomor WA sama tetapi nama anak (berbeda)
        $bookingAnak = Booking::create([
            'code' => Booking::generateUniqueCode(),
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => now()->toDateString(),
            'start_time' => '11:00',
            'patient_name' => 'Dimas (Anak Ibu Ratna)',
            'patient_whatsapp' => $sharedPhone,
            'source' => BookingSource::WHATSAPP,
            'status' => BookingStatus::VERIFIED,
            'slot_key' => "{$doctor->id}_" . now()->toDateString() . "_11:00",
        ]);

        // Pastikan keduanya TIDAK digabungkan menjadi 1 pasien
        $this->assertNotEquals($bookingIbu->patient_id, $bookingAnak->patient_id);

        $patientIbu = Patient::find($bookingIbu->patient_id);
        $patientAnak = Patient::find($bookingAnak->patient_id);

        $this->assertEquals('Ibu Ratna', $patientIbu->name);
        $this->assertEquals('Dimas (Anak Ibu Ratna)', $patientAnak->name);
        $this->assertEquals($sharedPhone, $patientIbu->whatsapp);
        $this->assertEquals($sharedPhone, $patientAnak->whatsapp);

        // 3. Booking ketiga untuk Ibu Ratna lagi (nama dan no WA sama) -> harus terhubung ke pasien yang sama
        $bookingIbuLagi = Booking::create([
            'code' => Booking::generateUniqueCode(),
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00',
            'patient_name' => 'Ibu Ratna',
            'patient_whatsapp' => $sharedPhone,
            'source' => BookingSource::WHATSAPP,
            'status' => BookingStatus::VERIFIED,
            'slot_key' => "{$doctor->id}_" . now()->addDays(2)->toDateString() . "_10:00",
        ]);

        $this->assertEquals($patientIbu->id, $bookingIbuLagi->patient_id);
        $this->assertEquals(2, $patientIbu->fresh()->bookings()->count());
    }

    public function test_booking_calendar_page_and_doctor_filter(): void
    {
        $doctor = Doctor::firstOrFail();

        $component = Livewire::actingAs($this->admin)
            ->test(BookingCalendar::class)
            ->assertSuccessful();

        $allEvents = $component->get('events');
        $this->assertNotEmpty($allEvents);

        // Filter per dokter
        $component->set('selectedDoctorId', $doctor->id);
        $filteredEvents = $component->get('events');

        foreach ($filteredEvents as $ev) {
            $this->assertEquals($doctor->name, $ev['extendedProps']['doctor_name']);
        }
    }

    public function test_patient_resource_lists_patients_and_visit_count(): void
    {
        $patient = Patient::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(PatientResource\Pages\ListPatients::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(Patient::limit(5)->get())
            ->assertTableActionExists('view_history', record: $patient)
            ->assertTableActionExists('chat', record: $patient);
    }
}

