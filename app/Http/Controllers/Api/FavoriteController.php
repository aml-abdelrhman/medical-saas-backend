<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * جلب مفضلات المريض
     * (ملاحظة: هذا المسار يجب أن يكون خلف middleware('auth:sanctum'))
     */
    public function index(Request $request)
    {
        return $request->user()->favorites()->with('doctor.specialty')->get();
    }

    /**
     * التبديل (Toggle): إذا موجود يحذف، إذا غير موجود يضيف
     */
    public function toggle(Request $request, $doctor_id)
    {
        $user = $request->user(); // المستخدم موثق تلقائياً بفضل الـ middleware

        try {
            // البحث عن السجل
            $favorite = Favorite::where('patient_id', $user->id)
                ->where('doctor_id', $doctor_id)
                ->first();

            // الحذف إذا كان موجوداً
            if ($favorite) {
                $favorite->delete();

                return response()->json([
                    'message' => 'Removed from favorites',
                    'status' => 'removed',
                ]);
            }

            // الإضافة إذا لم يكن موجوداً
            Favorite::create([
                'patient_id' => $user->id,
                'doctor_id' => $doctor_id,
            ]);

            return response()->json([
                'message' => 'Added to favorites',
                'status' => 'added',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}