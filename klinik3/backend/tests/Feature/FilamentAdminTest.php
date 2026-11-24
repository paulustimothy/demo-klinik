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

        $this->actingAs($admin)
            ->get('/admin/booking-report')
            ->assertSuccessful()
            ->assertSee('Laporan Bulanan Reservasi')
            ->assertSee('Angka No-Show')
            ->assertSee('Periode Laporan');
    }

    public function test_admin_can_mark_booking_as_no_show(): void
    {
        $booking = \App\Models\Booking::first();
        $this->assertNotNull($booking);

        $booking->status = \App\Enums\BookingStatus::VERIFIED;
        $booking->save();

        $this->assertTrue($booking->markNoShow());
        $this->assertEquals(\App\Enums\BookingStatus::NO_SHOW, $booking->status);
        $this->assertNull($booking->slot_key);
    }

    public function test_export_service_generates_csv_and_xlsx(): void
    {
        $exportService = app(\App\Services\ExportService::class);
        $bookings = \App\Models\Booking::with(['service', 'doctor'])->get();

        // 1. Export Bookings CSV
        $csvResponse = $exportService->exportBookings($bookings, 'csv');
        $this->assertEquals(200, $csvResponse->getStatusCode());
        $this->assertStringContainsString('.csv', $csvResponse->getFile()->getFilename());

        // 2. Export Bookings XLSX
        $xlsxResponse = $exportService->exportBookings($bookings, 'xlsx');
        $this->assertEquals(200, $xlsxResponse->getStatusCode());
        $this->assertStringContainsString('.xlsx', $xlsxResponse->getFile()->getFilename());
    }

    public function test_admin_can_export_monthly_report(): void
    {
        $exportService = app(\App\Services\ExportService::class);
        $page = new \App\Filament\Pages\BookingReport();
        $page->selectedMonth = 11;
        $page->selectedYear = 2026;
        $data = $page->getReportData();

        $xlsxResponse = $exportService->exportMonthlyReport(
            $data['summary'],
            $data['serviceStats'],
            $data['doctorStats'],
            $data['noShowList'],
            $data['monthName'],
            $data['year'],
            'xlsx'
        );
        $this->assertEquals(200, $xlsxResponse->getStatusCode());

        $csvResponse = $exportService->exportMonthlyReport(
            $data['summary'],
            $data['serviceStats'],
            $data['doctorStats'],
            $data['noShowList'],
            $data['monthName'],
            $data['year'],
            'csv'
        );
        $this->assertEquals(200, $csvResponse->getStatusCode());

        $pdfResponse = $exportService->exportMonthlyPdf(
            $data['summary'],
            $data['serviceStats'],
            $data['doctorStats'],
            $data['noShowList'],
            $data['monthName'],
            $data['year']
        );
        $this->assertEquals(200, $pdfResponse->getStatusCode());
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_database_backup_command(): void
    {
        $this->artisan('db:backup')
            ->expectsOutputToContain('Backup berhasil dibuat!')
            ->assertSuccessful();

        $backupService = app(\App\Services\DatabaseBackupService::class);
        $backups = $backupService->listBackups();
        $this->assertNotEmpty($backups);
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

