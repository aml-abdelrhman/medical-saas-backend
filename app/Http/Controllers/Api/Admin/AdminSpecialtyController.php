<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminSpecialtyController extends Controller
{
    // عرض جميع التخصصات الخاصة بعيادة الأدمن الحالي
    public function index(Request $request)
    {
        // استخراج الـ clinic_id من المستخدم الحالي أو الـ Request
        $user = $request->user();
        $clinicId = $user->clinic_id ?? $user->clinic?->id;

        $query = Specialty::query();
        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    // عرض تخصص واحد بالـ id
    public function show($id)
    {
        $specialty = Specialty::findOrFail($id);
        return response()->json(['success' => true, 'data' => $specialty]);
    }

    // إضافة تخصص جديد مرتبط بالـ clinic_id
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'slug' => 'required|unique:specialties,slug',
            'clinic_id' => 'required|exists:clinics,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $specialty = new Specialty();
        $specialty->name = $request->input('name');
        $specialty->description = $request->input('description');
        $specialty->slug = $request->input('slug');
        $specialty->clinic_id = $request->input('clinic_id');

        // تخزين الصورة محلياً وإرجاع الرابط كاملاً
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('specialties', 'public');
            // توليد الرابط الكامل مباشرة لتفادي أي مشاكل عرض
            $fullUrl = asset('storage/' . $path);
            $specialty->image = $path; // أو حفظ المسار النسبي أو $fullUrl حسب رغبتك في الداتابيز
            
            Log::info('=== ADMIN SPECIALTY STORE: LOCAL IMAGE ===', ['path' => $path, 'url' => $fullUrl]);
        }

        $specialty->save();

        return response()->json([
            'message' => 'تمت إضافة التخصص بنجاح', 
            'data' => $specialty
        ], 201);
    }

    // تحديث تخصص موجود (يدعم POST أو PUT)
    public function update(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        $request->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
            'slug' => 'sometimes|unique:specialties,slug,' . $id,
            'clinic_id' => 'sometimes|exists:clinics,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        Log::info('=== ADMIN SPECIALTY UPDATE: HAS FILE? ===', [
            'hasFile' => $request->hasFile('image'),
            'all_keys' => array_keys($request->all()),
        ]);

        if ($request->has('name')) $specialty->name = $request->input('name');
        if ($request->has('description')) $specialty->description = $request->input('description');
        if ($request->has('slug')) $specialty->slug = $request->input('slug');
        if ($request->has('clinic_id')) $specialty->clinic_id = $request->input('clinic_id');

        // تحديث الصورة محلياً
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إذا أردتِ تنظيف السيرفر
            if ($specialty->image && Storage::disk('public')->exists($specialty->image)) {
                Storage::disk('public')->delete($specialty->image);
            }

            $path = $request->file('image')->store('specialties', 'public');
            $specialty->image = $path;
            
            Log::info('=== ADMIN SPECIALTY UPDATE: NEW LOCAL IMAGE ===', ['path' => $path]);
        }

        $specialty->save();

        return response()->json([
            'message' => 'تم تحديث التخصص بنجاح', 
            'data' => $specialty
        ]);
    }

    // حذف تخصص
    public function destroy(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        if ($specialty->doctors()->count() > 0 && !$request->has('force')) {
            return response()->json(['message' => 'هذا التخصص مرتبط بأطباء!'], 409);
        }

        if ($request->has('force')) {
            $specialty->doctors()->delete();
        }

        // حذف الصورة المرتبطة من التخزين المحلي
        if ($specialty->image && Storage::disk('public')->exists($specialty->image)) {
            Storage::disk('public')->delete($specialty->image);
        }

        $specialty->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}