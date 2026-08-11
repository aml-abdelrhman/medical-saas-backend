<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    // عرض جميع المواعيد الخاصة بعيادة الأدمن الحالي فقط مع إمكانية الفلترة بالتاريخ
    public function index(Request $request)
    {
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? $user->clinic_id ?? $user->clinic?->id;

        $query = Appointment::with(['doctor.specialty', 'patient', 'service']);
        
        // تقييد المواعيد بناءً على رقم العيادة الخاص بالأدمن
        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        // فلترة اختيارية بالتاريخ إذا تم إرساله
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('appointment_date', $request->date);
        }

        $appointments = $query->latest()->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time ?? null,
                'status' => $appointment->status,
                'price' => isset($appointment->price) ? (float) $appointment->price : null,
                'patient' => $appointment->patient ? [
                    'id' => $appointment->patient->id,
                    'name' => $appointment->patient->name,
                    'phone' => $appointment->patient->phone ?? null,
                ] : null,
                'doctor' => $appointment->doctor ? [
                    'id' => $appointment->doctor->id,
                    'name' => $appointment->doctor->name,
                ] : null,
                'service' => $appointment->service ? [
                    'id' => $appointment->service->id,
                    'name' => $appointment->service->name,
                    'price' => (float) $appointment->service->price,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    // عرض تفاصيل موعد معين مع التأكد أنه تابع لنفس العيادة
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $clinicId = $user->clinic_id ?? $user->clinic?->id;

        $query = Appointment::with(['doctor.specialty', 'patient', 'service']);

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $appointment = $query->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time ?? null,
                'status' => $appointment->status,
                'patient' => $appointment->patient,
                'doctor' => $appointment->doctor,
                'service' => $appointment->service,
            ]
        ]);
    }

    // حذف موعد مع التحقق من تبعيته للعيادة
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $clinicId = $user->clinic_id ?? $user->clinic?->id;

        $query = Appointment::query();

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $appointment = $query->findOrFail($id);
        $appointment->delete();

        return response()->json([
            'success' => true, 
            'message' => 'تم حذف الموعد بنجاح'
        ]);
    }
}