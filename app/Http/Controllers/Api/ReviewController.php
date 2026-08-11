<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Doctor;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // 1. المريض: إضافة تقييم
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'required|unique:reviews,appointment_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|array',
        ]);

        $doctor = Doctor::findOrFail($request->doctor_id);

        $review = Review::create([
            'clinic_id' => $doctor->clinic_id, // تم إضافة الـ clinic_id هنا لتفادي أي مشاكل في حفظ السجل
            'patient_id' => auth()->id(),
            ...$validated
        ]);

        if ($doctor) {
            $doctor->update(['rating' => Review::where('doctor_id', $request->doctor_id)->avg('rating')]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التقييم بنجاح',
            'data' => $review
        ], 201);
    }

    // 2. الطبيب: عرض تقييماته فقط
    public function doctorReviews(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found',
                'debug_user_id' => auth()->id()
            ], 404);
        }

        $reviews = Review::where('doctor_id', $doctor->id)
                         ->with('patient:id,name')
                         ->latest()
                         ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    // 3. الأدمن: عرض كل التقييمات الخاصة بعيادته فقط
    public function index(Request $request)
    {
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? ($user?->clinic_id ?? $user?->clinic?->id);

        $query = Review::with(['doctor', 'patient']);

        if ($clinicId) {
            $query->whereHas('doctor', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            });
        }

        $reviews = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    // 4. الأدمن: مسح تقييم بأمان
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? ($user?->clinic_id ?? $user?->clinic?->id);

        $query = Review::with('doctor');

        if ($clinicId) {
            $query->whereHas('doctor', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            });
        }

        $review = $query->findOrFail($id);
        $doctor_id = $review->doctor_id;
        $review->delete();

        $doctor = Doctor::find($doctor_id);
        if ($doctor) {
            $doctor->update(['rating' => Review::where('doctor_id', $doctor_id)->avg('rating') ?? 0]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'تم حذف التقييم بنجاح'
        ]);
    }

    // 5. عام: عرض تقييمات أطباء العيادة مباشرة باستخدام الـ slug للزوار
    public function clinicPublicReviews($slug)
    {
        $clinic = \App\Models\Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $reviews = Review::whereHas('doctor', function ($q) use ($clinic) {
                $q->where('clinic_id', $clinic->id);
            })
            ->with(['doctor:id,name,image', 'patient:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}