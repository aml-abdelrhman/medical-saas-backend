<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        try {
            // استخراج الـ clinic_id من الـ Request أو من بيانات المستخدم المسجل
            $user = $request->user();
            $clinicId = $request->input('clinic_id') ?? ($user?->clinic_id ?? $user?->clinic?->id);

            // 1. حساب إجمالي الأطباء التابعين للعيادة
            $doctorsQuery = Doctor::query();
            if ($clinicId) {
                $doctorsQuery->where('clinic_id', $clinicId);
            }
            $totalDoctors = (int) $doctorsQuery->count();

            // 2. حساب إجمالي الخدمات التابعة للعيادة مباشرة
            $servicesQuery = Service::query();
            if ($clinicId) {
                $servicesQuery->where('clinic_id', $clinicId);
            }
            $totalServices = (int) $servicesQuery->count();

            // 3. حساب إجمالي المواعيد المرتبطة بأطباء العيادة
            $appointmentsQuery = Appointment::query();
            if ($clinicId) {
                $appointmentsQuery->whereHas('doctor', function ($q) use ($clinicId) {
                    $q->where('clinic_id', $clinicId);
                });
            }
            $newAppointments = (int) $appointmentsQuery->count();

            // 4. حساب إجمالي التقييمات عبر أطباء العيادة فقط مع معالجة الأخطاء بأمان
            $totalReviews = 0;
            try {
                $reviewsQuery = Review::query();
                if ($clinicId) {
                    $reviewsQuery->whereHas('doctor', function ($q) use ($clinicId) {
                        $q->where('clinic_id', $clinicId);
                    });
                }
                $totalReviews = (int) $reviewsQuery->count();
            } catch (\Exception $e) {
                Log::warning('Dashboard Reviews Count Warning: ' . $e->getMessage());
                $totalReviews = 0;
            }

            // 5. بيانات الرسم البياني (عدد المواعيد في آخر 7 أيام) مع فلترة العيادة
            $chartQuery = Appointment::query()
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(6)->startOfDay());

            if ($clinicId) {
                $chartQuery->whereHas('doctor', function ($q) use ($clinicId) {
                    $q->where('clinic_id', $clinicId);
                });
            }

            $rawChartData = $chartQuery->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'ASC')
                ->get()
                ->keyBy('date');

            // ملء الأيام التي لا تحتوي على مواعيد بصفر (اختياري لضمان استقرار الرسم البياني في الفرونت إند)
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $chartData[] = [
                    'date' => $date,
                    'count' => isset($rawChartData[$date]) ? (int) $rawChartData[$date]->count : 0
                ];
            }

            // 6. إرجاع البيانات بشكل منظم ومرتب للفرونت إند
            return response()->json([
                'success' => true,
                'message' => 'تم جلب إحصائيات لوحة التحكم بنجاح',
                'data' => [
                    'total_doctors' => $totalDoctors,
                    'total_services' => $totalServices,
                    'new_orders' => $newAppointments,
                    'total_reviews' => $totalReviews,
                    'chart_data' => $chartData
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Dashboard Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب إحصائيات لوحة التحكم',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}