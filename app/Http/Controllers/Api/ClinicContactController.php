<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ClinicContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClinicContactController extends Controller
{
    /**
     * حفظ رسالة "اتصل بنا" جديدة للعيادة
     */
    public function store(Request $request, $slug)
    {
        $clinic = Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'message' => 'required|string|max:1000',
            ]);

            $validated['clinic_id'] = $clinic->id;

            $contactMessage = ClinicContactMessage::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'تم إرسال رسالتك إلى العيادة بنجاح',
                'data' => $contactMessage
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل التحقق من البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ في الخادم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // عرض رسائل "اتصل بنا" الخاصة بالعيادة
public function index(Request $request)
{
    try {
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? $user->clinic_id ?? $user->clinic?->id;

        $query = ClinicContactMessage::with('clinic');

        // إذا لم يكن سوبر أدمن، نقوم بتصفية الرسائل لتخص عيادته فقط
        if ($user->role !== 'super_admin' && $clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $messages = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء جلب الرسائل',
            'error' => $e->getMessage()
        ], 500);
    }
}

// دالة حذف رسالة تواصل
public function destroy($id)
{
    try {
        $message = ClinicContactMessage::findOrFail($id);
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الرسالة بنجاح'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء حذف الرسالة'
        ], 500);
    }
}
}