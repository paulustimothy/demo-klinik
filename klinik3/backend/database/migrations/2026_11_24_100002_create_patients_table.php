<?php

use App\Models\Booking;
use App\Models\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('whatsapp', 20);
            $table->timestamps();

            // Indeks pencarian gabungan nama & nomor WhatsApp
            $table->index(['name', 'whatsapp']);
            $table->index('whatsapp');
        });

        // 2. Tambahkan foreign key patient_id pada tabel bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('doctor_id')->constrained('patients')->nullOnDelete();
        });

        // 3. Backfill data pasien dari booking yang sudah ada
        if (Schema::hasTable('bookings')) {
            $bookings = Booking::all();
            foreach ($bookings as $booking) {
                if ($booking->patient_name && $booking->patient_whatsapp) {
                    $patient = Patient::firstOrCreate([
                        'name' => trim($booking->patient_name),
                        'whatsapp' => $booking->patient_whatsapp,
                    ]);
                    $booking->patient_id = $patient->id;
                    $booking->saveQuietly();
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn('patient_id');
        });

        Schema::dropIfExists('patients');
    }
};

