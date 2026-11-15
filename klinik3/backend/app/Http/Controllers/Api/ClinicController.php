<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicSetting;
use Illuminate\Http\JsonResponse;

class ClinicController extends Controller
{
    public function show(): JsonResponse
    {
        $clinic = ClinicSetting::instance();

        return response()->json([
            'clinic_name' => $clinic->clinic_name,
            'tagline' => $clinic->tagline,
            'address' => $clinic->address,
            'hours_text' => $clinic->hours_text,
            'whatsapp_number' => $clinic->whatsapp_number,
            'email' => $clinic->email,
            'hold_minutes' => $clinic->hold_minutes,
            'slot_minutes' => $clinic->slot_minutes,
        ]);
    }
}

