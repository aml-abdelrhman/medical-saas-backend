<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DoctorServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // إذا كان الطبيب مسجل دخول، نجلب خدماته وخدمات عيادته
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        $query = Service::query();

        if ($doctor) {
            $query->where('doctor_id', $doctor->id);
            // ربط بالكلينك آي دي إذا كان متوفراً في جدول الطبيب
            if ($doctor->clinic_id) {
                // إذا كان جدول الخدمات يحتوي على عمود clinic_id
                // $query->orWhere('clinic_id', $doctor->clinic_id);
            }
        }

        // إمكانية الفلترة المباشرة عبر الـ Request إذا تم إرسال clinic_id
        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $services = $query->get()->map(function($service) {
            return [
                'id' => $service->id,
                'doctor_id' => $service->doctor_id,
                'clinic_id' => $service->clinic_id ?? null,
                'name' => is_string($service->name) ? json_decode($service->name, true) : $service->name,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'is_active' => $service->is_active,
                'image_url' => $service->image ? (str_starts_with($service->image, 'http') ? $service->image : asset('storage/' . $service->image)) : null
            ];
        });
        
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $data = $request->except(['image', '_method']);
        $user = auth()->user();
        
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if ($doctor) {
            $data['doctor_id'] = $doctor->id;
            // جلب الـ clinic_id تلقائياً من بيانات الطبيب أو المستخدم
            $data['clinic_id'] = $doctor->clinic_id ?? $user->clinic_id ?? null;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path; 
        }

        $service = Service::create($data);
        
        return response()->json([
            'success' => true, 
            'message' => 'تم الإضافة بنجاح',
            'data' => $service
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $service = Service::where('id', $id)->firstOrFail();
        $data = $request->except(['image', '_method']);

        // الحفاظ على ربط الـ clinic_id إذا لم يتم إرساله أو تحديثه
        $user = auth()->user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor && !isset($data['clinic_id'])) {
            $data['clinic_id'] = $doctor->clinic_id ?? $user->clinic_id ?? $service->clinic_id;
        }

        if ($request->hasFile('image')) {
            $rawImage = $service->getAttributes()['image'] ?? $service->image;
            if ($rawImage) {
                $relativePath = str_replace('/storage/', '', parse_url($rawImage, PHP_URL_PATH) ?? $rawImage);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
            
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        $service->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح',
            'data' => $service
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        $service = Service::where('id', $id)->where('doctor_id', $doctor?->id)->firstOrFail();
        
        $rawImage = $service->getAttributes()['image'] ?? $service->image;
        if ($rawImage) {
            $relativePath = str_replace('/storage/', '', parse_url($rawImage, PHP_URL_PATH) ?? $rawImage);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
        
        $service->delete();
        
        return response()->json([
            'success' => true, 
            'message' => 'Deleted successfully'
        ]);
    }
}