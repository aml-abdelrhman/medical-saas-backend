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
        $user = $request->user();
        $clinicId = $user->clinic_id ?? $user->clinic?->id;

        $query = Specialty::query();
        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $specialties = $query->get()->map(function ($specialty) {
            return $this->formatSpecialty($specialty);
        });

        return response()->json(['success' => true, 'data' => $specialties]);
    }

    // عرض تخصص واحد بالـ id
    public function show($id)
    {
        $specialty = Specialty::findOrFail($id);
        return response()->json(['success' => true, 'data' => $this->formatSpecialty($specialty)]);
    }

    // إضافة تخصص جديد مرتبط بالـ clinic_id
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required',
            'description' => 'required',
            'slug'        => 'required|unique:specialties,slug',
            'clinic_id'   => 'required|exists:clinics,id',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048'
        ]);

        $specialty = new Specialty();
        $specialty->name = $request->input('name');
        $specialty->description = $request->input('description');
        $specialty->slug = $request->input('slug');
        $specialty->clinic_id = $request->input('clinic_id');

        // رفع الصورة إلى Cloudinary باستخدام Storage Disk
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('specialties', 'cloudinary');
            $uploadedFileUrl = Storage::disk('cloudinary')->url($path);
            
            $specialty->image = $uploadedFileUrl; 
            Log::info('=== ADMIN SPECIALTY STORE: CLOUDINARY IMAGE ===', ['url' => $uploadedFileUrl]);
        }

        $specialty->save();

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة التخصص بنجاح', 
            'data'    => $this->formatSpecialty($specialty)
        ], 201);
    }

    // تحديث تخصص موجود (يدعم POST أو PUT)
    public function update(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        $request->validate([
            'name'        => 'sometimes',
            'description' => 'sometimes',
            'slug'        => 'sometimes|unique:specialties,slug,' . $id,
            'clinic_id'   => 'sometimes|exists:clinics,id',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048'
        ]);

        Log::info('=== ADMIN SPECIALTY UPDATE: HAS FILE? ===', [
            'hasFile'  => $request->hasFile('image'),
            'all_keys' => array_keys($request->all()),
        ]);

        if ($request->has('name')) $specialty->name = $request->input('name');
        if ($request->has('description')) $specialty->description = $request->input('description');
        if ($request->has('slug')) $specialty->slug = $request->input('slug');
        if ($request->has('clinic_id')) $specialty->clinic_id = $request->input('clinic_id');

        // رفع الصورة الجديدة عبر Cloudinary باستخدام Storage Disk
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('specialties', 'cloudinary');
            $uploadedFileUrl = Storage::disk('cloudinary')->url($path);
            
            $specialty->image = $uploadedFileUrl;
            Log::info('=== ADMIN SPECIALTY UPDATE: NEW CLOUDINARY IMAGE ===', ['url' => $uploadedFileUrl]);
        }

        $specialty->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التخصص بنجاح', 
            'data'    => $this->formatSpecialty($specialty)
        ]);
    }

    // حذف تخصص
    public function destroy(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        if ($specialty->doctors()->count() > 0 && !$request->has('force')) {
            return response()->json(['success' => false, 'message' => 'هذا التخصص مرتبط بأطباء!'], 409);
        }

        if ($request->has('force')) {
            $specialty->doctors()->delete();
        }

        // الصور على Cloudinary لا تتطلب حذفاً محلياً معقداً
        $specialty->delete();
        
        return response()->json(['success' => true, 'message' => 'تم الحذف بنجاح']);
    }

    // دالة مساعدة لتنسيق البيانات وإرجاعها بشكل نظيف
    private function formatSpecialty($specialty)
    {
        return [
            'id'          => $specialty->id,
            'clinic_id'   => $specialty->clinic_id,
            'name'        => is_string($specialty->name) ? (json_decode($specialty->name, true) ?? $specialty->name) : $specialty->name,
            'description' => is_string($specialty->description) ? (json_decode($specialty->description, true) ?? $specialty->description) : $specialty->description,
            'slug'        => $specialty->slug,
            'image'       => $specialty->image,
        ];
    }
}