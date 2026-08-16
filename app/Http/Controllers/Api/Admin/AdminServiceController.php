<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class AdminServiceController extends Controller
{
    // عرض جميع الخدمات الخاصة بعيادة الأدمن الحالي
    public function index(Request $request)
    {
        $user = $request->user();
        $clinicId = $user->clinic_id ?? $user->clinic?->id;

        $query = Service::with(['doctor.specialty', 'clinic']);
        
        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $services = $query->get()->map(function ($service) {
            return $this->formatService($service);
        });

        return response()->json(['success' => true, 'data' => $services]);
    }

    // إضافة خدمة جديدة مرتبطة بالكلينك إي دي ورفع الصورة إلى Cloudinary
    public function store(Request $request)
    {
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? $user->clinic_id ?? $user->clinic?->id;

        $validated = $request->validate([
            'doctor_id'        => 'required|exists:doctors,id',
            'name'             => 'required',
            'price'            => 'required|numeric',
            'duration_minutes' => 'required|integer',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        if (!$clinicId) {
            return response()->json(['success' => false, 'message' => 'رقم العيادة غير متوفر'], 422);
        }

        $validated['clinic_id'] = $clinicId;

        // رفع الصورة مباشرة إلى Cloudinary باستخدام Storage Disk
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'cloudinary');
            $uploadedFileUrl = Storage::disk('cloudinary')->url($path);
            
            $validated['image'] = $uploadedFileUrl; 
            Log::info('=== ADMIN SERVICE STORE: CLOUDINARY IMAGE UPLOADED ===', ['url' => $uploadedFileUrl]);
        }

        try {
            $service = Service::create($validated);
            
            return response()->json([
                'success' => true, 
                'message' => 'تمت الإضافة بنجاح', 
                'data'    => $this->formatService($service)
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('=== ADMIN SERVICE STORE ERROR ===', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()], 500);
        }
    }

    // تحديث خدمة موجودة
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'doctor_id'        => 'sometimes|exists:doctors,id',
            'name'             => 'sometimes',
            'price'            => 'sometimes|numeric',
            'duration_minutes' => 'sometimes|integer',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        // رفع الصورة الجديدة عبر Cloudinary باستخدام Storage Disk
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'cloudinary');
            $uploadedFileUrl = Storage::disk('cloudinary')->url($path);
            
            $validated['image'] = $uploadedFileUrl;
            
            Log::info('=== ADMIN SERVICE UPDATE: NEW CLOUDINARY IMAGE ===', ['url' => $uploadedFileUrl]);
        }

        $service->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح', 
            'data'    => $this->formatService($service)
        ]);
    }

    // حذف خدمة
    public function destroy(Request $request, $id)
    {
        try {
            $service = Service::withoutGlobalScopes()->findOrFail($id);

            $user = $request->user();
            $userRole = $user->role ?? 'admin'; 
            $userClinicId = $user->clinic_id ?? $user->clinic?->id;

            if ($userRole !== 'super_admin' && $userClinicId) {
                if ($service->clinic_id != $userClinicId) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'غير مصرح لك بحذف خدمة تخص عيادة أخرى'
                    ], 403);
                }
            }

            // الصور على Cloudinary لا تحتاج إلى حذف محلي معقد
            $service->delete();

            return response()->json([
                'success' => true, 
                'message' => 'تم حذف الخدمة نهائياً بنجاح'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'الخدمة غير موجودة'
            ], 404);
        } catch (QueryException $e) {
            Log::error('Service Delete Foreign Key Error: ' . $e->getMessage());
            
            if ($e->getCode() == "23000" || str_contains($e->getMessage(), 'foreign key constraint fails')) {
                return response()->json([
                    'success' => false, 
                    'message' => 'لا يمكن حذف هذه الخدمة لوجود حجوزات سابقة مرتبطة بها في النظام'
                ], 422);
            }

            return response()->json([
                'success' => false, 
                'message' => 'حدث خطأ في قاعدة البيانات أثناء الحذف'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Service Delete Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'حدث خطأ غير متوقع أثناء عملية الحذف'
            ], 500);
        }
    }

    // دالة مساعدة لتنسيق بيانات الخدمة وإرجاعها بشكل نظيف
    private function formatService($service)
    {
        return [
            'id'               => $service->id,
            'clinic_id'        => $service->clinic_id,
            'doctor_id'        => $service->doctor_id,
            'name'             => is_string($service->name) ? (json_decode($service->name, true) ?? $service->name) : $service->name,
            'price'            => (float) $service->price,
            'duration_minutes' => (int) $service->duration_minutes,
            'image'            => $service->image, 
            'is_active'        => (bool) $service->is_active,
            'doctor'           => $service->doctor ? [
                'id'   => $service->doctor->id,
                'name' => is_string($service->doctor->name) ? (json_decode($service->doctor->name, true) ?? $service->doctor->name) : $service->doctor->name,
            ] : null,
        ];
    }
}