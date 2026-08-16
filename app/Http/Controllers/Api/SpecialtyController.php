<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    // عرض التخصصات الخاصة بالعيادة
    public function index(Request $request)
    {
        $user = $request->user();
        $clinicId = $request->filled('clinic_id') && optional($user)->role === 'super_admin'
            ? $request->input('clinic_id')
            : optional($user)->clinic_id;

        $query = Specialty::query();

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        } else {
            return response()->json(['status' => true, 'data' => []]);
        }

        $specialties = $query->get()->map(function ($item) {
            return $this->formatSpecialty($item);
        });

        return response()->json([
            'status' => true,
            'data'   => $specialties,
        ], 200);
    }

    // إضافة تخصص جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|array',
            'description' => 'required|array',
            'slug'        => 'required|unique:specialties,slug',
            'clinic_id'   => 'required|exists:clinics,id'
        ]);

        $specialty = Specialty::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تمت إضافة التخصص بنجاح', 
            'data'    => $this->formatSpecialty($specialty)
        ], 201);
    }

    // تحديث تخصص موجود
    public function update(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|array',
            'description' => 'sometimes|array',
            'slug'        => 'sometimes|unique:specialties,slug,' . $id,
            'clinic_id'   => 'sometimes|exists:clinics,id'
        ]);

        $specialty->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث التخصص بنجاح', 
            'data'    => $this->formatSpecialty($specialty)
        ]);
    }

    // حذف تخصص مع إمكانية الحذف القسري للأطباء المرتبطين
    public function destroy(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        if ($specialty->doctors()->exists() && !$request->has('force')) {
            return response()->json([
                'status'  => false,
                'message' => 'هذا التخصص مرتبط بأطباء!'
            ], 409);
        }

        if ($request->has('force')) {
            $specialty->doctors()->delete(); 
        }

        $specialty->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم الحذف بنجاح'
        ]);
    }

    // عرض تفاصيل تخصص معين عبر الـ slug
    public function show($slug)
    {
        $specialty = Specialty::where('slug', $slug)->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $this->formatSpecialty($specialty)
        ]);
    }

    // دالة مساعدة موحدة لتنسيق بيانات التخصص
    private function formatSpecialty($specialty)
    {
        return [
            'id'          => $specialty->id,
            'clinic_id'   => $specialty->clinic_id,
            'name'        => is_string($specialty->name) ? (json_decode($specialty->name, true) ?? $specialty->name) : $specialty->name,
            'description' => is_string($specialty->description) ? (json_decode($specialty->description, true) ?? $specialty->description) : $specialty->description,
            'slug'        => $specialty->slug
        ];
    }
}