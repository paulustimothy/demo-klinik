<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function index(): JsonResponse
    {
        $doctors = Doctor::with(['schedules' => function ($query) {
            $query->orderBy('weekday');
        }])
            ->where('is_active', true)
            ->get()
            ->map(function ($doctor) {
                $practiceDays = $doctor->schedules->pluck('weekday')->unique()->values()->all();
                
                $weekdayNames = [
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                ];
                $practiceDaysText = collect($practiceDays)
                    ->map(fn($day) => $weekdayNames[$day] ?? '')
                    ->filter()
                    ->join(', ');

                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'title' => $doctor->title,
                    'years_experience' => $doctor->years_experience,
                    'photo_path' => $doctor->photo_url ?: $doctor->photo_path,
                    'practice_days' => $practiceDays,
                    'practice_days_text' => $practiceDaysText,
                    'schedules' => $doctor->schedules->map(fn($schedule) => [
                        'weekday' => $schedule->weekday,
                        'weekday_name' => $schedule->weekdayName(),
                        'start_time' => substr($schedule->start_time, 0, 5),
                        'end_time' => substr($schedule->end_time, 0, 5),
                    ]),
                ];
            });

        return response()->json($doctors);
    }
}

