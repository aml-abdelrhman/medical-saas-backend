<?php
namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;

class DoctorAppointmentController extends Controller
{
    public function index()
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        
        return Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctor->id)
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function cancel($id)
    {
        // 1. التأكد أن الموعد يخص الطبيب الحالي
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        
        $appointment = Appointment::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        // 2. تحديث الحالة بدلاً من الحذف
        $appointment->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Appointment cancelled successfully']);
    }

    public function complete($id)
    {
        // 1. التأكد أن الموعد يخص الطبيب الحالي
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        
        $appointment = Appointment::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        // 2. تحديث الحالة إلى مكتمل
        $appointment->update(['status' => 'completed']);

        return response()->json(['message' => 'Appointment marked as completed successfully']);
    }
}

