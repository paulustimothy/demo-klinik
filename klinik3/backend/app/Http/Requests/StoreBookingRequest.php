<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi nomor WhatsApp: hapus karakter non-angka, konversi 08xx -> 628xx
        if ($this->has('whatsapp')) {
            $rawPhone = preg_replace('/[^\d]/', '', (string) $this->whatsapp);
            if (str_starts_with($rawPhone, '08')) {
                $rawPhone = '62' . substr($rawPhone, 1);
            }
            $this->merge(['normalized_whatsapp' => $rawPhone]);
        }
    }

    public function rules(): array
    {
        $today = Carbon::today()->toDateString();
        $maxDate = Carbon::today()->addDays(14)->toDateString();

        return [
            'service_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $service = Service::find($value);
                    if (!$service || !$service->is_active) {
                        $fail('Layanan yang dipilih tidak aktif atau tidak ditemukan.');
                    }
                },
            ],
            'doctor_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $doctor = Doctor::find($value);
                    if (!$doctor || !$doctor->is_active) {
                        $fail('Dokter yang dipilih tidak aktif atau tidak ditemukan.');
                    }
                },
            ],
            'date' => [
                'required',
                'date_format:Y-m-d',
                "after_or_equal:{$today}",
                "before_or_equal:{$maxDate}",
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    // Klinik tutup hari Minggu
                    if ($date->dayOfWeekIso === 7) {
                        $fail('Klinik tutup pada hari Minggu.');
                        return;
                    }

                    // Cek jadwal praktik dokter pada hari tersebut
                    $doctorId = $this->input('doctor_id');
                    if ($doctorId) {
                        $doctor = Doctor::find($doctorId);
                        if ($doctor) {
                            $hasSchedule = $doctor->schedules()
                                ->where('weekday', $date->dayOfWeekIso)
                                ->exists();
                            if (!$hasSchedule) {
                                $fail('Dokter tidak berpraktik pada hari yang dipilih.');
                            }
                        }
                    }
                },
            ],
            'time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $doctorId = $this->input('doctor_id');
                    $dateStr = $this->input('date');

                    if ($doctorId && $dateStr) {
                        $date = Carbon::parse($dateStr);
                        $doctor = Doctor::find($doctorId);

                        if ($doctor && $date->dayOfWeekIso !== 7) {
                            $schedule = $doctor->schedules()
                                ->where('weekday', $date->dayOfWeekIso)
                                ->first();

                            if ($schedule) {
                                $start = Carbon::createFromTimeString($schedule->start_time)->format('H:i');
                                $end = Carbon::createFromTimeString($schedule->end_time)->format('H:i');

                                if ($value < $start || $value >= $end) {
                                    $fail("Jam harus berada dalam rentang jadwal praktik dokter ({$start} - {$end}).");
                                    return;
                                }

                                // Cek jika hari ini, jam tidak boleh sudah lewat
                                if ($date->isToday()) {
                                    $slotDateTime = Carbon::parse("{$dateStr} {$value}");
                                    if ($slotDateTime->lte(Carbon::now())) {
                                        $fail('Jam yang dipilih sudah lewat untuk hari ini.');
                                    }
                                }
                            }
                        }
                    }
                },
            ],
            'name' => ['required', 'string', 'max:100'],
            'whatsapp' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $phone = $this->input('normalized_whatsapp');
                    // Format valid Indonesia: 628 diikuti 8-12 digit angka
                    if (!$phone || !preg_match('/^628\d{8,12}$/', $phone)) {
                        $fail('Nomor WhatsApp harus berformat 08xx atau 628xx yang valid.');
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Layanan wajib dipilih.',
            'service_id.integer' => 'Format layanan tidak valid.',
            'doctor_id.required' => 'Dokter wajib dipilih.',
            'doctor_id.integer' => 'Format dokter tidak valid.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'date.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'date.before_or_equal' => 'Reservasi maksimal 14 hari ke depan.',
            'time.required' => 'Jam wajib dipilih.',
            'time.date_format' => 'Format jam harus HH:MM.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'notes.max' => 'Catatan maksimal 200 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Data reservasi tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}

