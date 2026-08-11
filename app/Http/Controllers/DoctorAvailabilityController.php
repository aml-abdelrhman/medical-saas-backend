<?php

namespace App\Http\Controllers;

use App\Models\DoctorAvailability;
use Illuminate\Http\Request;

class DoctorAvailabilityController extends Controller
{
    /**
     * 1. عرض الأوقات المتاحة
     */
  public function index(Request $request)
{
    // 1. عرض عام (بدون تسجيل دخول - للمرضى)
    if ($request->has('doctor_id') && !auth()->check()) {
        return DoctorAvailability::where('doctor_id', $request->doctor_id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->get(['id', 'day_of_week', 'day_name', 'start_time', 'end_time']);
    }

    // 2. التحقق من المستخدم المسجل
    $user = auth()->user();
    if (!$user) {
        return response()->json(['message' => 'غير مصرح'], 401);
    }

    $query = DoctorAvailability::query();

    // 3. منطق الطبيب (تعديل: نبحث عن الـ doctor_id المرتبط بالـ user_id)
    if ($user->role === 'doctor') {
        // نستخدم الـ ID مباشرة من جدول الأطباء بدلاً من الاعتماد على العلاقة المباشرة
        $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return response()->json(['message' => 'بيانات الطبيب غير موجودة في قاعدة البيانات'], 404);
        }
        $query->where('doctor_id', $doctor->id);
    } 
    // 4. منطق الأدمن
    elseif ($user->role === 'admin' && $request->has('doctor_id')) {
        $query->where('doctor_id', $request->doctor_id);
    }
    // 5. في حال لم يكن أدمن ولم يكن طبيب أو طلب بدون صلاحيات
    else {
        return response()->json(['message' => 'غير مصرح'], 403);
    }

    return $query->orderBy('day_of_week')->get();
}

 
  /**
     * .  للطبيب إضافة موعد جديد (يوم جديد في جدول الطبيب)
     */
/**
     * إضافة موعد جديد (تعتمد على نفس منطق index في جلب الطبيب)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // جلب الطبيب بنفس طريقة الـ index
        $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return response()->json(['message' => 'بيانات الطبيب غير موجودة'], 404);
        }

        $request->validate([
            'day_of_week' => 'required|integer|between:0,6|unique:doctor_availabilities,day_of_week,NULL,id,doctor_id,' . $doctor->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        DoctorAvailability::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'تم إضافة الموعد بنجاح']);
    }

    /**
     * تعديل موعد (تعتمد على نفس منطق index في جلب الطبيب)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $availability = DoctorAvailability::findOrFail($id);

        // إذا كان أدمن يمر فوراً
        if ($user->role === 'admin') {
            // مسموح
        } 
        // إذا كان طبيب، نبحث عن الـ ID الخاص به بنفس منطق الـ index
        elseif ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            
            if (!$doctor || $availability->doctor_id !== $doctor->id) {
                return response()->json(['message' => 'لا تملك صلاحية تعديل هذا الموعد'], 403);
            }
        } else {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $availability->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json(['message' => 'تم التحديث بنجاح']);
    }
    /**
     * 3. حذف ميعاد معين
     */
/**
     * 3. حذف ميعاد معين (يدعم الطبيب والأدمن)
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $availability = DoctorAvailability::findOrFail($id);

        // 1. إذا كان المستخدم أدمن، نسمح له بالحذف فوراً
        if ($user->role === 'admin') {
            $availability->delete();
            return response()->json(['message' => 'تم حذف الموعد بواسطة الأدمن']);
        }

        // 2. إذا كان طبيب، نتأكد من ملكيته للموعد
        if ($user->role === 'doctor') {
            // نبحث عن الطبيب بنفس منطق الـ index والـ update لضمان الدقة
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            
            if (!$doctor || $availability->doctor_id !== $doctor->id) {
                return response()->json(['message' => 'لا تملك صلاحية حذف هذا الموعد'], 403);
            }

            $availability->delete();
            return response()->json(['message' => 'تم حذف الموعد بنجاح']);
        }

        // 3. إذا لم يكن أياً منهما
        return response()->json(['message' => 'غير مصرح'], 403);
    }
}