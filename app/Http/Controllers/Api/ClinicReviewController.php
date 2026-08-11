<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ClinicReview; // التصحيح هنا ليتوافق مع الجدول والـ Model الصحيح
use Illuminate\Http\Request;

class ClinicReviewController extends Controller
{
    // 1. عرض تقييمات المنصة المعتمدة للعامة والزوار
    public function index()
    {
        $reviews = ClinicReview::where('is_approved', true)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    // 2. إضافة تقييم جديد للمنصة من قبل الطبيب أو صاحب العيادة
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_name'   => 'required|string|max:255',
            'clinic_name'   => 'nullable|string|max:255',
            'doctor_avatar' => 'nullable|string',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string|max:1000',
        ]);

        $review = ClinicReview::create([
            'doctor_id'   => auth()->id(),
            'is_approved' => false, // يحتاج موافقة الأدمن قبل ظهوره بالواجهة
            ...$validated
        ]);

        return response()->json([
            'success' => true,
            'message' => 'شكراً لك! تم إرسال تقييمك للمنصة بنجاح وسيتم مراجعته وعرضه قريباً.',
            'data' => $review
        ], 201);
    }

    // 3. عرض جميع التقييمات للأدمن (المعتمدة وغير المعتمدة)
    public function adminIndex()
    {
        $reviews = ClinicReview::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    // 4. تغيير حالة اعتماد التقييم (قبول أو إخفاء)
    public function toggleApproval($id)
    {
        $review = ClinicReview::findOrFail($id);
        
        $review->update([
            'is_approved' => !$review->is_approved
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة التقييم بنجاح',
            'data' => $review
        ]);
    }

    // 5. حذف التقييم نهائياً بواسطة الأدمن
    public function destroy($id)
    {
        $review = ClinicReview::findOrFail($id);
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقييم بنجاح'
        ]);
    }
}