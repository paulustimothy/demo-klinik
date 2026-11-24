<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'description' => $service->description,
                    'duration_minutes' => $service->duration_minutes,
                    'starting_price' => $service->starting_price,
                    'image_path' => $service->image_url ?: $service->image_path,
                    'sort_order' => $service->sort_order,
                ];
            });

        return response()->json($services);
    }
}

