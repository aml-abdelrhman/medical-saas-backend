<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $services = $query->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'clinic_id' => $service->clinic_id,
                'doctor_id' => $service->doctor_id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                'image' => $service->image, // استخدام الـ Attribute مباشرة بدون دوال
                'is_active' => (bool) $service->is_active
            ];
        });

        return response()->json(['success' => true, 'data' => $services]);
    }

    public function show($id)
    {
        $service = Service::with(['doctor.specialty', 'clinic'])->findOrFail($id);
        
        $formattedService = [
            'id' => $service->id,
            'clinic_id' => $service->clinic_id,
            'doctor_id' => $service->doctor_id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'duration_minutes' => (int) $service->duration_minutes,
            'image' => $service->image, // استخدام الـ Attribute مباشرة
            'is_active' => (bool) $service->is_active,
            'doctor' => $service->doctor ? [
                'id' => $service->doctor->id,
                'name' => $service->doctor->name,
                'specialty_id' => $service->doctor->specialty_id,
            ] : null,
        ];

        return response()->json(['success' => true, 'data' => $formattedService]);
    }

    public function getServicesWithSpecialties()
    {
        $services = Service::with('doctor.specialty', 'clinic')
            ->whereHas('doctor')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'clinic_id' => $service->clinic_id,
                    'name' => $service->name,
                    'price' => (float) $service->price,
                    'duration_minutes' => (int) $service->duration_minutes,
                    'image' => $service->image, // استخدام الـ Attribute مباشرة
                    'doctor' => $service->doctor ? [
                        'id' => $service->doctor->id,
                        'name' => $service->doctor->name,
                        'specialty_id' => $service->doctor->specialty_id,
                    ] : null,
                    'specialty_name' => $service->doctor?->specialty?->name ?? null
                ];
            });

        return response()->json(['success' => true, 'data' => $services]);
    }
}