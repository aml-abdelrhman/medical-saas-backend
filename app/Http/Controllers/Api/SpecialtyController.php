<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    // عرض جميع التخصصات الخاصة بالعيادة الحالية فقط
public function index(Request $request)
{
    // هوية الأدمن هي المصدر الأساسي للـ clinic_id، مش الباراميتر القادم من الفرونت
    $user = $request->user();
    $clinicId = $request->filled('clinic_id') && optional($user)->role === 'super_admin'
        ? $request->input('clinic_id')
        : optional($user)->clinic_id;

    $query = Specialty::query();

    if ($clinicId) {
        $query->where('clinic_id', $clinicId);
    } else {
        // لو مفيش clinic_id خالص (لا من التوكن ولا من الباراميتر)، منرجعش كل حاجة بالغلط
        return response()->json(['status' => true, 'data' => []]);
    }

    return response()->json([
        'status' => true,
        'data' => $query->get(),
    ], 200);
}    // إضافة تخصص جديد للعيادة
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'description' => 'required|array',
            'slug' => 'required|unique:specialties,slug',
            'clinic_id' => 'required|exists:clinics,id' // التأكد من إرسال معرف العيادة
        ]);

        $specialty = new Specialty();
        $specialty->name = $request->input('name');
        $specialty->description = $request->input('description');
        $specialty->slug = $request->input('slug');
        $specialty->clinic_id = $request->input('clinic_id'); // ربط التخصص بالعيادة
        
        $specialty->save();

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة التخصص بنجاح', 
            'data' => $specialty
        ], 201);
    }

    // تحديث تخصص موجود
    public function update(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|array',
            'description' => 'sometimes|array',
            'slug' => 'sometimes|unique:specialties,slug,' . $id,
            'clinic_id' => 'sometimes|exists:clinics,id'
        ]);

        if ($request->has('name')) $specialty->name = $request->input('name');
        if ($request->has('description')) $specialty->description = $request->input('description');
        if ($request->has('slug')) $specialty->slug = $request->input('slug');
        if ($request->has('clinic_id')) $specialty->clinic_id = $request->input('clinic_id');

        $specialty->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث التخصص بنجاح', 
            'data' => $specialty
        ]);
    }

    // حذف تخصص
    public function destroy(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        // التحقق من وجود أطباء مرتبطين بهذا التخصص
        if ($specialty->doctors()->count() > 0 && !$request->has('force')) {
            return response()->json([
                'status' => false,
                'message' => 'هذا التخصص مرتبط بأطباء!'
            ], 409);
        }

        // إذا تم إرسال طلب الحذف الإجباري، احذف الأطباء المرتبطين أولاً
        if ($request->has('force')) {
            $specialty->doctors()->delete(); 
        }

        $specialty->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم الحذف بنجاح'
        ]);
    }

    // عرض تخصص محدد بواسطة الـ slug
    public function show($slug)
    {
        $specialty = Specialty::where('slug', $slug)->first();

        if (!$specialty) {
            return response()->json([
                'status' => false,
                'message' => 'Specialty not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $specialty
        ]);
    }
}