<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // عرض مواعيد المريض الشخصية (تم جعل الـ clinicSlug اختيارياً لمنع أخطاء الـ Arguments)
    public function myAppointments($clinicSlug = null)
    {
        $query = Appointment::with(['doctor', 'service', 'clinic'])
            ->where('patient_id', auth()->id());

        // إذا تم إرسال الـ slug، قم بالتصفية بناءً على العيادة
        if ($clinicSlug) {
            $clinic = Clinic::where('slug', $clinicSlug)->first();
            if ($clinic) {
                $query->where('clinic_id', $clinic->id);
            }
        }

        return $query->latest()->get();
    }

    // إنشاء حجز جديد داخل العيادة المحددة
   public function store(Request $request, $clinicSlug)
    {
        $clinic = Clinic::where('slug', $clinicSlug)->firstOrFail();

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required', // تم إزالة شرط الصيغة المعقد ليرتاح البال تماماً
        ]);

        $service = Service::where('id', $request->service_id)->where('clinic_id', $clinic->id)->firstOrFail();
        $doctor = Doctor::where('id', $request->doctor_id)->where('clinic_id', $clinic->id)->firstOrFail();
        
        // معالجة آمنة للوقت أياً كانت الصيغة القادمة من الفرت إند
        try {
            $startTime = Carbon::parse($request->start_time);
        } catch (\Exception $e) {
            return response()->json(['message' => 'صيغة الوقت غير صحيحة'], 422);
        }

        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        // منع التضارب: هل هناك حجز في نفس الفترة لنفس الطبيب داخل نفس العيادة؟
        $exists = Appointment::where('doctor_id', $request->doctor_id)
            ->where('clinic_id', $clinic->id)
            ->where('appointment_date', $request->appointment_date)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime->format('H:i'), $endTime->format('H:i')])
                      ->orWhereBetween('end_time', [$startTime->format('H:i'), $endTime->format('H:i')]);
            })->exists();

        if ($exists) {
            return response()->json(['message' => 'هذا الموعد محجوز مسبقاً'], 422);
        }

        return Appointment::create([
            'clinic_id' => $clinic->id,
            'patient_id' => auth()->id(),
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'status' => 'confirmed'
        ]);
    }

    // إلغاء الحجز
    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);
        if (auth()->id() !== $appointment->patient_id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }
        $appointment->update(['status' => 'cancelled']);
        return response()->json(['message' => 'تم إلغاء الموعد']);
    }
}