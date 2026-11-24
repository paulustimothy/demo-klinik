<?php

namespace Tests\Feature;

use Database\Seeders\ClinicSettingSeeder;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicApiTest extends TestCase
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

    public function test_get_clinic_info_returns_expected_fields(): void
    {
        $response = $this->getJson('/api/clinic');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'clinic_name',
                'tagline',
                'address',
                'hours_text',
                'whatsapp_number',
                'email',
                'hold_minutes',
                'slot_minutes',
            ])
            ->assertJson([
                'clinic_name' => 'Klinik Gigi Senyum Sehat',
                'hold_minutes' => 120,
            ]);
    }

    public function test_get_services_returns_active_services_ordered(): void
    {
        $response = $this->getJson('/api/services');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'duration_minutes',
                    'starting_price',
                    'image_path',
                    'sort_order',
                ],
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertNotNull($item['image_path']);
            $this->assertMatchesRegularExpression('/^https?:\/\//', $item['image_path']);
        }
    }

    public function test_get_doctors_returns_active_doctors_with_schedules(): void
    {
        $response = $this->getJson('/api/doctors');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'title',
                    'years_experience',
                    'photo_path',
                    'practice_days',
                    'practice_days_text',
                    'schedules',
                ],
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertNotNull($item['photo_path']);
            $this->assertMatchesRegularExpression('/^https?:\/\//', $item['photo_path']);
        }
    }
}

