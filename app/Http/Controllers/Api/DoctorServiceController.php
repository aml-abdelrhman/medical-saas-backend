<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DoctorServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $doctor = Doctor::where('user_id', $user?->id)->first();
        
        $query = Service::query();

        if ($doctor) {
            $query->where('doctor_id', $doctor->id);
        }

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
                // دعم روابط Cloudinary المباشرة أو الروابط القديمة
                'image_url' => $service->image 
                    ? (str_starts_with($service->image, 'http') ? $service->image : asset('storage/' . $service->image)) 
                    : null
            ];
        });
        
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        // إضافة Validation لمنع أخطاء 422 المفاجئة وعرض رسائل واضحة
        $validated = $request->validate([
            'name'             => 'required',
            'price'            => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active'        => 'nullable|boolean',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $validated;
        
        if ($doctor) {
            $data['doctor_id'] = $doctor->id;
            $data['clinic_id'] = $doctor->clinic_id ?? $user->clinic_id ?? null;
        }

        // رفع الصورة إلى Cloudinary باستخدام Storage Disk الموحد
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'cloudinary');
            $data['image'] = Storage::disk('cloudinary')->url($path);
            Log::info('=== SERVICE STORE: CLOUDINARY IMAGE UPLOADED ===', ['url' => $data['image']]);
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
        
        $validated = $request->validate([
            'name'             => 'sometimes|required',
            'price'            => 'sometimes|required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active'        => 'nullable|boolean',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $validated;

        $user = auth()->user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor && !isset($data['clinic_id'])) {
            $data['clinic_id'] = $doctor->clinic_id ?? $user->clinic_id ?? $service->clinic_id;
        }

        // رفع الصورة الجديدة عبر Cloudinary باستخدام Storage Disk الموحد
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'cloudinary');
            $data['image'] = Storage::disk('cloudinary')->url($path);
            Log::info('=== SERVICE UPDATE: CLOUDINARY IMAGE UPLOADED ===', ['url' => $data['image']]);
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
        
        // الحذف من قاعدة البيانات مباشرة (الصور على Cloudinary لا تحتاج لحذف محلي معقد)
        $service->delete();
        
        return response()->json([
            'success' => true, 
            'message' => 'Deleted successfully'
        ]);
    }
}