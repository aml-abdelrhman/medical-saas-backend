<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DoctorServiceController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\Admin\AdminServiceController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\Admin\AdminAppointmentController;
use App\Http\Controllers\Api\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\AdminSpecialtyController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\ClinicContactController;
use App\Http\Controllers\Api\SuperAdmin\SuperAdminController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Controllers\Api\ClinicSubscriptionController; // <-- تأكدي من إضافة هذا السطر بالأعلى
use App\Http\Controllers\Api\ClinicReviewController;
use App\Http\Controllers\ContactMessageController;

// 1. المسارات العامة
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/specialties', [SpecialtyController::class, 'index']);
Route::get('/specialties/{slug}', [SpecialtyController::class, 'show']);
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services-with-specialties', [ServiceController::class, 'getServicesWithSpecialties']);
Route::get('/availabilities', [DoctorAvailabilityController::class, 'index']);
Route::get('/plans', [ClinicController::class, 'getPlans']);
Route::get('/clinics/{slug}/doctors-reviews', [ReviewController::class, 'clinicPublicReviews']);
Route::get('/platform-reviews', [ClinicReviewController::class, 'index']);
Route::post('/contact-messages', [ContactMessageController::class, 'store']);

Route::prefix('v1')->group(function () {
    Route::post('/clinics/register', [ClinicController::class, 'store']);
    Route::get('/clinics/{slug}', [ClinicController::class, 'show']);
    Route::get('/clinics/{slug}/doctors', [ClinicController::class, 'getDoctors']);
    Route::get('/clinics/{slug}/specialties', [ClinicController::class, 'getSpecialties']);
    Route::get('/clinics/{slug}/services', [ClinicController::class, 'getServices']);
    Route::get('/clinics/{slug}/reviews', [ClinicReviewController::class, 'show']);
    Route::post('/clinics/{slug}/contact', [ClinicContactController::class, 'store']);
Route::post('/subscriptions/checkout', [ClinicSubscriptionController::class, 'subscribe']);
  });

// 2. المسارات المحمية للمستخدمين (المرضى)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/doctor/profile', [DoctorController::class, 'myProfile']);
    
   // المواعيد (تم تعديل مسار الـ store ليحتوي على clinicSlug ليتطابق مع الـ Controller)
    Route::get('/clinics/{clinicSlug}/my-appointments', [AppointmentController::class, 'myAppointments']);
    Route::post('/clinics/{clinicSlug}/appointments', [AppointmentController::class, 'store']); // <-- التصحيح هنا
    Route::put('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
   
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{doctor_id}', [FavoriteController::class, 'toggle']);
});

// 3. مسارات الأدمن (مدير العيادة) - محمية بالصلاحية واشتراك العيادة
Route::middleware(['auth:sanctum', 'role:admin', 'subscription.active'])->prefix('admin')->group(function () {
    // التخصصات
    Route::get('/specialties', [AdminSpecialtyController::class, 'index']);
    Route::get('/specialties/{id}', [AdminSpecialtyController::class, 'show']);
    Route::post('/specialties', [AdminSpecialtyController::class, 'store']);
    Route::post('/specialties/{id}', [AdminSpecialtyController::class, 'update']);
    Route::delete('/specialties/{id}', [AdminSpecialtyController::class, 'destroy']);

    // المستخدمون
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']); // هذا الـ Route الجديد
    Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // الأطباء
    Route::get('/doctors', [AdminDoctorController::class, 'index']);
    Route::post('/doctors', [AdminDoctorController::class, 'store']);
    Route::get('/doctors/{id}', [AdminDoctorController::class, 'show']);
    Route::match(['post', 'put'], '/doctors/{id}', [AdminDoctorController::class, 'update']);
    Route::delete('/doctors/{id}', [AdminDoctorController::class, 'destroy']);

    // الخدمات
    Route::apiResource('services', AdminServiceController::class);
    Route::get('/doctor-services', [AdminServiceController::class, 'index']);

    // المواعيد والمراجعات
    Route::get('/availability', [DoctorAvailabilityController::class, 'index']);
    Route::delete('/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);
    Route::get('/appointments', [AdminAppointmentController::class, 'index']);
    Route::delete('/appointments/{id}', [AdminAppointmentController::class, 'destroy']);
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/dashboard-stats', [DashboardController::class, 'getDashboardStats']);

Route::get('/contact-messages', [ClinicContactController::class, 'index']);

    });

// 4. مسارات الطبيب - محمية بصلاحية الطبيب واشتراك العيادة
Route::middleware(['auth:sanctum', 'is_doctor', 'subscription.active'])->prefix('doctor')->group(function () {
    Route::post('/profile/{id}', [DoctorController::class, 'update']);
    Route::get('/services', [DoctorServiceController::class, 'index']);
    Route::post('/services', [DoctorServiceController::class, 'store']);
    Route::put('/services/{id}', [DoctorServiceController::class, 'update']);
    Route::delete('/services/{id}', [DoctorServiceController::class, 'destroy']);
    Route::get('/my-availability', [DoctorAvailabilityController::class, 'index']);
    Route::post('/update-schedule', [DoctorAvailabilityController::class, 'updateSchedule']);
    Route::delete('/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);
    Route::post('/availability', [DoctorAvailabilityController::class, 'store']);
    Route::put('/availability/{id}', [DoctorAvailabilityController::class, 'update']);
    Route::get('/appointments', [DoctorAppointmentController::class, 'index']);
    Route::patch('/appointments/{id}/cancel', [DoctorAppointmentController::class, 'cancel']);
    Route::post('/appointments/{id}/complete', [DoctorAppointmentController::class, 'complete']);
    Route::get('/reviews', [ReviewController::class, 'doctorReviews']);
});

// 5. مسارات الـ Super Admin (مدير المنصة العام) - محمية بـ SuperAdminMiddleware
Route::middleware(['auth:sanctum', SuperAdminMiddleware::class])->prefix('super-admin')->group(function () {
    
    // إحصائيات المنصة العامة
    Route::get('/stats', [SuperAdminController::class, 'getPlatformStats']);

    // إدارة الباقات
    Route::get('/plans', [SuperAdminController::class, 'indexPlans']);
    Route::post('/plans', [SuperAdminController::class, 'storePlan']);
    Route::put('/plans/{id}', [SuperAdminController::class, 'updatePlan']);
    Route::delete('/plans/{id}', [SuperAdminController::class, 'destroyPlan']);

   // 4. إدارة الاشتراكات (Subscriptions)
    Route::get('/subscriptions', [SuperAdminController::class, 'indexSubscriptions']);
    Route::post('/subscriptions', [SuperAdminController::class, 'storeSubscription']);
    Route::put('/subscriptions/{id}', [SuperAdminController::class, 'updateSubscription']);
    Route::delete('/subscriptions/{id}', [SuperAdminController::class, 'destroySubscription']);
    
    Route::get('/contact-messages', [ContactMessageController::class, 'index']);
    // إدارة العيادات بالكامل واشتراكاتها
    Route::get('/clinics', [SuperAdminController::class, 'indexClinics']);
    Route::delete('/clinics/{id}', [SuperAdminController::class, 'destroyClinic']);
Route::post('/clinics', [SuperAdminController::class, 'storeClinic']);       // <-- إضافة عيادة جديدة
    Route::put('/clinics/{id}', [SuperAdminController::class, 'updateClinic']);   // <-- تعديل عيادة موجودة
    // إدارة جميع مستخدمين المنصة
    Route::get('/users', [SuperAdminController::class, 'indexUsers']);
    Route::delete('/users/{id}', [SuperAdminController::class, 'destroyUser']);
});