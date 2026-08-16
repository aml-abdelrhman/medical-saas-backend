<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // تم إضافتها لدعم الروابط

class ServiceController extends Controller
{
    // ... (باقي الدوال index, show, getServicesWithSpecialties تبقى كما هي)

    /**
     * دالة مساعدة موحدة لتنسيق بيانات الخدمة الأساسية
     * تم تحديثها لتوحيد التعامل مع صور Cloudinary
     */
    private function formatService($service)
    {
        return [
            'id'               => $service->id,
            'clinic_id'        => $service->clinic_id,
            'doctor_id'        => $service->doctor_id,
            'name'             => is_string($service->name) ? (json_decode($service->name, true) ?? $service->name) : $service->name,
            'price'            => (float) $service->price,
            'duration_minutes' => (int) $service->duration_minutes,
            // توحيد منطق عرض الصورة ليتطابق مع باقي الكنترولات
            'image'            => $service->image 
                ? (str_starts_with($service->image, 'http') ? $service->image : asset('storage/' . $service->image)) 
                : null,
            'is_active'        => (bool) $service->is_active
        ];
    }
}