<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Booking::truncate();
        Schema::enableForeignKeyConstraints();

        $services = Service::all()->keyBy('slug');
        $doctors = Doctor::all()->values();

        if ($doctors->count() < 3 || $services->count() < 3) {
            return;
        }

        $docRina = $doctors[0]; // Practices: Mon(1), Wed(3), Fri(5)
        $docBudi = $doctors[1]; // Practices: Tue(2), Thu(4), Sat(6)
        $docMaya = $doctors[2]; // Practices: Mon(1), Thu(4), Sat(6)

        $now = Carbon::now();

        // 1. Pending: Doctor Rina next Monday
        $date1 = Carbon::parse('next monday')->toDateString();
        $time1 = '09:00';
        Booking::create([
            'code' => 'KG-A8X9K2',
            'service_id' => $services['scaling']->id,
            'doctor_id' => $docRina->id,
            'date' => $date1,
            'start_time' => $time1,
            'patient_name' => 'Siti Nurhaliza',
            'patient_whatsapp' => '6281234567801',
            'notes' => 'Pembersihan karang gigi rutin',
            'status' => BookingStatus::PENDING,
            'slot_key' => Booking::generateSlotKey($docRina->id, $date1, $time1),
            'expires_at' => $now->copy()->addMinutes(90),
            'verified_at' => null,
        ]);

        // 2. Pending: Doctor Budi next Tuesday
        $date2 = Carbon::parse('next tuesday')->toDateString();
        $time2 = '10:00';
        Booking::create([
            'code' => 'KG-M4T7P9',
            'service_id' => $services['tambal']->id,
            'doctor_id' => $docBudi->id,
            'date' => $date2,
            'start_time' => $time2,
            'patient_name' => 'Ahmad Fauzi',
            'patient_whatsapp' => '6281234567802',
            'notes' => 'Gigi geraham belakang ngilu saat makan',
            'status' => BookingStatus::PENDING,
            'slot_key' => Booking::generateSlotKey($docBudi->id, $date2, $time2),
            'expires_at' => $now->copy()->addMinutes(45),
            'verified_at' => null,
        ]);

        // 3. Verified: Doctor Rina next Wednesday
        $date3 = Carbon::parse('next wednesday')->toDateString();
        $time3 = '11:00';
        Booking::create([
            'code' => 'KG-H3N8R5',
            'service_id' => $services['scaling']->id,
            'doctor_id' => $docRina->id,
            'date' => $date3,
            'start_time' => $time3,
            'patient_name' => 'Dewi Lestari',
            'patient_whatsapp' => '6281234567803',
            'notes' => 'Sudah konfirmasi via WhatsApp',
            'status' => BookingStatus::VERIFIED,
            'slot_key' => Booking::generateSlotKey($docRina->id, $date3, $time3),
            'expires_at' => null,
            'verified_at' => $now->copy()->subHours(2),
        ]);

        // 4. Verified: Doctor Maya next Thursday
        $date4 = Carbon::parse('next thursday')->toDateString();
        $time4 = '14:00';
        Booking::create([
            'code' => 'KG-Y7W2C8',
            'service_id' => $services['cabut']->id,
            'doctor_id' => $docMaya->id,
            'date' => $date4,
            'start_time' => $time4,
            'patient_name' => 'Bambang Sudarmono',
            'patient_whatsapp' => '6281234567804',
            'notes' => 'Konsultasi cabut gigi geraham bungsu',
            'status' => BookingStatus::VERIFIED,
            'slot_key' => Booking::generateSlotKey($docMaya->id, $date4, $time4),
            'expires_at' => null,
            'verified_at' => $now->copy()->subHours(4),
        ]);

        // 5. Completed: Doctor Budi last Tuesday
        $date5 = Carbon::parse('last tuesday')->toDateString();
        $time5 = '13:00';
        Booking::create([
            'code' => 'KG-K9L3F6',
            'service_id' => $services['tambal']->id,
            'doctor_id' => $docBudi->id,
            'date' => $date5,
            'start_time' => $time5,
            'patient_name' => 'Rina Wijaya',
            'patient_whatsapp' => '6281234567805',
            'notes' => 'Penambalan selesai rapi',
            'status' => BookingStatus::COMPLETED,
            'slot_key' => null,
            'expires_at' => null,
            'verified_at' => $now->copy()->subDays(3),
        ]);

        // 6. Completed: Doctor Rina last Friday
        $date6 = Carbon::parse('last friday')->toDateString();
        $time6 = '15:00';
        Booking::create([
            'code' => 'KG-T2J8V4',
            'service_id' => $services['scaling']->id,
            'doctor_id' => $docRina->id,
            'date' => $date6,
            'start_time' => $time6,
            'patient_name' => 'Eko Prasetyo',
            'patient_whatsapp' => '6281234567806',
            'notes' => null,
            'status' => BookingStatus::COMPLETED,
            'slot_key' => null,
            'expires_at' => null,
            'verified_at' => $now->copy()->subDays(2),
        ]);

        // 7. Cancelled: Doctor Maya last Thursday
        $date7 = Carbon::parse('last thursday')->toDateString();
        $time7 = '16:00';
        Booking::create([
            'code' => 'KG-B5D9X1',
            'service_id' => $services['cabut']->id,
            'doctor_id' => $docMaya->id,
            'date' => $date7,
            'start_time' => $time7,
            'patient_name' => 'Indah Permata',
            'patient_whatsapp' => '6281234567807',
            'notes' => 'Pasien berhalangan hadir',
            'status' => BookingStatus::CANCELLED,
            'slot_key' => null,
            'expires_at' => null,
            'verified_at' => null,
        ]);

        // 8. Expired: Doctor Budi yesterday/past date
        $date8 = Carbon::parse('last saturday')->toDateString();
        $time8 = '10:00';
        Booking::create([
            'code' => 'KG-Z6P4N7',
            'service_id' => $services['tambal']->id,
            'doctor_id' => $docBudi->id,
            'date' => $date8,
            'start_time' => $time8,
            'patient_name' => 'Gunawan Wibowo',
            'patient_whatsapp' => '6281234567808',
            'notes' => null,
            'status' => BookingStatus::EXPIRED,
            'slot_key' => null,
            'expires_at' => $now->copy()->subHours(5),
            'verified_at' => null,
        ]);
    }
}

