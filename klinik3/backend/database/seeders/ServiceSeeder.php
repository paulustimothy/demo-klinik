<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Service::truncate();
        Schema::enableForeignKeyConstraints();

        $services = [
            [
                'name' => 'Scaling Gigi',
                'slug' => 'scaling',
                'description' => 'Pembersihan karang gigi ultrasonik yang lembut dan menyegarkan napas.',
                'duration_minutes' => 45,
                'starting_price' => 300000,
                'image_path' => '/images/service-scaling.webp',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Tambal Gigi',
                'slug' => 'tambal',
                'description' => 'Menambal gigi berlubang menggunakan resin komposit estetik warna alami.',
                'duration_minutes' => 30,
                'starting_price' => 250000,
                'image_path' => '/images/service-filling.webp',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Cabut Gigi',
                'slug' => 'cabut',
                'description' => 'Pencabutan gigi aman dan minim ngilu dengan bius lokal modern terukur.',
                'duration_minutes' => 30,
                'starting_price' => 350000,
                'image_path' => '/images/service-extraction.webp',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}

