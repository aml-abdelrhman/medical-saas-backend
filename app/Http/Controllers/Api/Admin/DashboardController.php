<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        // استخراج الـ clinic_id من الـ Request أو من بيانات المستخدم المسجل
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? ($user?->clinic_id ?? $user?->clinic?->id);

        // 1. حساب إجمالي الأطباء التابعين للعيادة
        $doctorsQuery = Doctor::query();
        if ($clinicId) {
            $doctorsQuery->where('clinic_id', $clinicId);
        }
        $totalDoctors = $doctorsQuery->count();

        // 2. حساب إجمالي الخدمات المرتبطة بأطباء العيادة
        $servicesQuery = Service::query();
        if ($clinicId) {
            $servicesQuery->whereHas('doctor', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            });
        }
        $totalServices = $servicesQuery->count();

        // 3. حساب إجمالي المواعيد المرتبطة بأطباء العيادة
        $appointmentsQuery = Appointment::query();
        if ($clinicId) {
            $appointmentsQuery->whereHas('doctor', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            });
        }
        $newAppointments = $appointmentsQuery->count();

        // 4. حساب إجمالي التقييمات عبر أطباء العيادة فقط (بدون أي استخدام لـ reviews.clinic_id)
        $totalReviews = 0;
        try {
            $reviewsQuery = Review::query();
            if ($clinicId) {
                $reviewsQuery->whereHas('doctor', function ($q) use ($clinicId) {
                    $q->where('clinic_id', $clinicId);
                });
            }
            $totalReviews = $reviewsQuery->count();
        } catch (\Exception $e) {
            // في حال حدث أي خطأ في جدول التقييمات، يتم جعله 0 مؤقتاً لتجنب تعطل الـ Dashboard بالكامل
            $totalReviews = 0;
        }

        // 5. بيانات الرسم البياني (عدد المواعيد في آخر 7 أيام) مع فلترة العيادة
        $chartQuery = Appointment::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(6)->startOfDay());

        if ($clinicId) {
            $chartQuery->whereHas('doctor', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            });
        }

        $chartData = $chartQuery->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 6. إرجاع كل البيانات في API واحد للفرونت إيند
        return response()->json([
            'success' => true,
            'data' => [
                'total_doctors' => $totalDoctors,
                'total_services' => $totalServices,
                'new_orders' => $newAppointments,
                'total_reviews' => $totalReviews,
                'chart_data' => $chartData
            ]
        ]);
    }
}