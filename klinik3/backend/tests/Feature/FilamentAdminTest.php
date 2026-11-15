<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\BookingSeeder;
use Database\Seeders\ClinicSettingSeeder;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_dashboard_and_resources(): void
    {
        $admin = User::where('email', env('ADMIN_EMAIL', 'admin@kliniksenyumsehat.com'))->first();
        $this->assertNotNull($admin);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSuccessful()
            ->assertSee('Klinik Gigi Senyum Sehat')
            ->assertSee('Data demo, direset setiap malam');

        $this->actingAs($admin)
            ->get('/admin/bookings')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->get('/admin/services')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->get('/admin/doctors')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->get('/admin/manage-clinic-settings')
            ->assertSuccessful();
    }

    public function test_chat_url_changes_when_booking_is_cancelled(): void
    {
        $admin = User::where('email', env('ADMIN_EMAIL', 'admin@kliniksenyumsehat.com'))->first();
        $booking = \App\Models\Booking::first();
        $this->assertNotNull($booking);

        $booking->status = \App\Enums\BookingStatus::VERIFIED;
        $booking->save();

        $component = \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\BookingResource\Pages\ListBookings::class);

        $chatAction = $component->instance()->getTable()->getAction('chat');
        $verifiedUrl = $chatAction->record($booking)->getUrl();
        $this->assertStringContainsString('sudah+dikonfirmasi', $verifiedUrl);

        // Uji jika dibatalkan
        $booking->status = \App\Enums\BookingStatus::CANCELLED;
        $booking->save();

        $cancelledUrl = $chatAction->record($booking)->getUrl();
        $this->assertStringContainsString('booking+telah+dibatalkan', $cancelledUrl);
    }
}

