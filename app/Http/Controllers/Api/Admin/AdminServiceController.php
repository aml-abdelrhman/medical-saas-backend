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
            return [
                'id' => $service->id,
                'clinic_id' => $service->clinic_id,
                'doctor_id' => $service->doctor_id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                // الـ Model Attribute سيقوم بإرجاع الرابط الكامل تلقائياً هنا
                'image' => $service->image, 
                'is_active' => (bool) $service->is_active,
                'doctor' => $service->doctor ? [
                    'id' => $service->doctor->id,
                    'name' => $service->doctor->name,
                ] : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $services]);
    }

    // إضافة خدمة جديدة مرتبطة بالكلينك إي دي وتخزينها في مجلد services
    public function store(Request $request)
    {
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? $user->clinic_id ?? $user->clinic?->id;

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'name'      => 'required',
            'price'     => 'required|numeric',
            'duration_minutes' => 'required|integer',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (!$clinicId) {
            return response()->json(['success' => false, 'message' => 'رقم العيادة غير متوفر'], 422);
        }

        $validated['clinic_id'] = $clinicId;

        // رفع الصورة وتخزينها مباشرة في C:\...\storage\app\public\services
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $validated['image'] = $path; // حفظ المسار النسبي (مثلاً: services/xxx.png) في قاعدة البيانات
            Log::info('=== ADMIN SERVICE STORE: IMAGE UPLOADED ===', ['path' => $path]);
        }

        try {
            $service = Service::create($validated);
            
            return response()->json([
                'success' => true, 
                'message' => 'تمت الإضافة بنجاح', 
                'data' => [
                    'id' => $service->id,
                    'clinic_id' => $service->clinic_id,
                    'doctor_id' => $service->doctor_id,
                    'name' => $service->name,
                    'price' => (float) $service->price,
                    'duration_minutes' => (int) $service->duration_minutes,
                    'image' => $service->image, // سيستفيد من الـ Attribute لإرجاع الرابط الكامل
                    'is_active' => (bool) $service->is_active,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('=== ADMIN SERVICE STORE ERROR ===', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'خطأ في قاعدة البيانات'], 500);
        }
    }

    // تحديث خدمة موجودة
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'doctor_id' => 'sometimes|exists:doctors,id',
            'name'      => 'sometimes',
            'price'     => 'sometimes|numeric',
            'duration_minutes' => 'sometimes|integer',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // التعامل مع رفع الصورة الجديدة وحذف القديمة إن وجدت
        if ($request->hasFile('image')) {
            // استخراج المسار الأصلي بدون الرابط الكامل للحذف الصحيح من التخزين
            $oldImage = $service->getRawOriginal('image');
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            $path = $request->file('image')->store('services', 'public');
            $validated['image'] = $path;
            
            Log::info('=== ADMIN SERVICE UPDATE: NEW IMAGE ===', ['path' => $path]);
        }

        $service->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح', 
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'image' => $service->image,
            ]
        ]);
    }

 // حذف خدمة والصورة المرتبطة بها
public function destroy(Request $request, $id)
{
    try {
        // 1. جلب الخدمة بدون التقيد بنطاق العيادة مؤقتاً للتأكد من وجودها
        $service = Service::withoutGlobalScopes()->findOrFail($id);

        // 2. جلب بيانات الأدمن الحالي
        $user = $request->user();
        $userRole = $user->role ?? 'admin'; 
        $userClinicId = $user->clinic_id ?? $user->clinic?->id;

        // 3. التحقق: إذا لم يكن سوبر أدمن، يجب أن تنتمي الخدمة لعيادة هذا الأدمن
        if ($userRole !== 'super_admin' && $userClinicId) {
            if ($service->clinic_id != $userClinicId) {
                return response()->json([
                    'success' => false, 
                    'message' => 'غير مصرح لك بحذف خدمة تخص عيادة أخرى'
                ], 403);
            }
        }

        // 4. حذف الصورة المرتبطة من التخزين إن وجدت
        $oldImage = $service->getRawOriginal('image');
        if ($oldImage && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        // 5. الحذف النهائي من قاعدة البيانات
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
        
        // التحقق إذا كان الخطأ بسبب وجود حجوزات مرتبطة (Foreign Key Constraint)
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
}