<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use Illuminate\Support\Facades\DB;
use App\Models\Appointment;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\User;
use App\Models\ClinicSubscription;
use Illuminate\Http\Request;


class SuperAdminController extends Controller
{
    /**
     * 1. إحصائيات المنصة العامة للسوبر أدمن
     */
public function getPlatformStats()
{
    $clinicsCount = Clinic::count();
    $doctorsCount = User::where('role', 'doctor')->count();
    $patientsCount = User::where('role', 'patient')->count();
    $adminsCount = User::where('role', 'admin')->count();
    $activeSubscriptions = ClinicSubscription::where('status', 'active')->count();

    // حساب إجمالي أرباح المنصة من الاشتراكات النشطة (أو جميع الاشتراكات غير الملغاة حسب رغبتك)
    // هنا نقوم بجمع سعر الباقة المرשطة بكل اشتراك نشط
    $totalRevenue = ClinicSubscription::where('status', 'active')
        ->join('plans', 'clinic_subscriptions.plan_id', '=', 'plans.id')
        ->sum('plans.price');

    return response()->json([
        'status' => 'success',
        'data' => [
            'total_clinics' => $clinicsCount,
            'total_doctors' => $doctorsCount,
            'total_patients' => $patientsCount,
            'total_admins' => $adminsCount,
            'active_subscriptions' => $activeSubscriptions,
            'total_revenue' => $totalRevenue, // إجمالي الأرباح الحقيقي
        ]
    ], 200);
}    /**
     * 2. عرض جميع الباقات المتاحة
     */
    public function indexPlans()
    {
        $plans = Plan::all();
        return response()->json([
            'status' => 'success',
            'data' => $plans
        ], 200);
    }

    /**
     * 3. إضافة باقة جديدة
     *//**
     * 3. إضافة باقة جديدة
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_in_days' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'max_doctors' => 'required|integer|min:1',   // <-- أضف هذا السطر
            'max_patients' => 'required|integer|min:1',  // <-- أضف هذا السطر
        ]);

        $plan = Plan::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الباقة بنجاح',
            'data' => $plan
        ], 201);
    }

    /**
     * 4. تعديل باقة موجودة
     */
    public function updatePlan(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_in_days' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string',
            'max_doctors' => 'sometimes|required|integer|min:1',   // <-- أضف هذا السطر
            'max_patients' => 'sometimes|required|integer|min:1',  // <-- أضف هذا السطر
        ]);

        $plan->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الباقة بنجاح',
            'data' => $plan
        ], 200);
    }

    /**
     * 5. حذف باقة
     */
    public function destroyPlan($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الباقة بنجاح'
        ], 200);
    }

    /**
     * 6. عرض جميع عيادات المنصة مع بيانات المالك والاشتراك
     */
    public function indexClinics()
    {
        $clinics = Clinic::with(['owner', 'subscription.plan'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $clinics
        ], 200);
    }

    /**
     * 11. إضافة عيادة جديدة مع حساب مالكها (Admin) من السوبر أدمن
     */
   /**
     * 11. إضافة عيادة جديدة مع حساب مالكها (Admin) من السوبر أدمن
     */
    public function storeClinic(Request $request)
    {
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:clinics,slug',
        'owner_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:255',
        'password' => 'required|string|min:6',
        'address' => 'nullable|string',
        'status' => 'nullable|in:active,expired,suspended,trial',
        'plan_id' => 'nullable|exists:plans,id',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 1. إضافة التحقق من الصورة
    ]);

    try {
        $clinic = DB::transaction(function () use ($request, $validated) {
            // 2. معالجة رفع الشعار إن وجد
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('clinics-logos', 'public');
            }

            // 1. إنشاء حساب مالك العيادة
            $owner = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'role' => 'admin',
            ]);

            // 2. إنشاء العيادة وإضافة مسار الـ logo
            $clinic = Clinic::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'owner_name' => $validated['owner_name'],
                'user_id' => $owner->id,
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'logo' => $logoPath, // 3. حفظ المسار في قاعدة البيانات
            ]);

            if (\Schema::hasColumn('users', 'clinic_id')) {
                $owner->update(['clinic_id' => $clinic->id]);
            }

            $planId = $validated['plan_id'] ?? Plan::first()?->id;

            ClinicSubscription::create([
                'clinic_id' => $clinic->id,
                'plan_id' => $planId,
                'status' => $validated['status'] ?? 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]);

            return $clinic;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء العيادة وحساب المالك بنجاح',
            'data' => $clinic->load(['owner', 'subscription.plan'])
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ أثناء إنشاء العيادة',
            'error' => $e->getMessage()
        ], 500);
    }
}
    }
    /**
     * 12. تعديل بيانات عيادة موجودة
     */
 /**
     * 12. تعديل بيانات عيادة موجودة
     */
    public function updateClinic(Request $request, $id)
    {
        $clinic = Clinic::with('owner')->findOrFail($id);

      $validated = $request->validate([
    'name' => 'sometimes|required|string|max:255',
    'slug' => 'sometimes|required|string|max:255|unique:clinics,slug,' . $clinic->id,
    'email' => 'sometimes|required|email|unique:users,email,' . optional($clinic->owner)->id,
    'phone' => 'nullable|string|max:255',
    'password' => 'nullable|string|min:6',
    'address' => 'nullable|string',
    'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
]);

if ($request->hasFile('logo')) {
    $logoPath = $request->file('logo')->store('clinics-logos', 'public');
    $clinic->logo = $logoPath;
}

if (isset($validated['name'])) $clinic->name = $validated['name'];
if (isset($validated['slug'])) $clinic->slug = $validated['slug'];
if (isset($validated['address'])) $clinic->address = $validated['address'];
if (isset($validated['phone'])) $clinic->phone = $validated['phone'];
$clinic->save();

if (optional($clinic->owner)->id) {
    $owner = $clinic->owner;
    if (isset($validated['email'])) $owner->email = $validated['email'];
    if (!empty($validated['password'])) $owner->password = bcrypt($validated['password']);
    $owner->save();
}

        try {
            DB::transaction(function () use ($request, $validated, $clinic) {
                // 1. تحديث بيانات العيادة الأساسية
                $clinicUpdateData = [];
                if (isset($validated['name'])) $clinicUpdateData['name'] = $validated['name'];
                if (isset($validated['slug'])) $clinicUpdateData['slug'] = $validated['slug'];
                if (isset($validated['email'])) $clinicUpdateData['email'] = $validated['email'];
                if (array_key_exists('phone', $validated)) $clinicUpdateData['phone'] = $validated['phone'];
                if (array_key_exists('address', $validated)) $clinicUpdateData['address'] = $validated['address'];

                // ✅ معالجة وتخزين الشعار الجديد في حال إرساله
                if ($request->hasFile('logo')) {
                    // حذف الشعار القديم إذا أردت تنظيف السيرفر (اختياري)
                    if ($clinic->logo && \Storage::disk('public')->exists($clinic->logo)) {
                        \Storage::disk('public')->delete($clinic->logo);
                    }
                    
                    // حفظ الشعار الجديد في مجلد clinics داخل الـ storage
                    $path = $request->file('logo')->store('clinics', 'public');
                    $clinicUpdateData['logo'] = $path;
                }

                if (!empty($clinicUpdateData)) {
                    $clinic->update($clinicUpdateData);
                }

                // 2. تحديث بيانات المالك المرتبط بالعيادة
                if ($clinic->owner) {
                    $ownerUpdateData = [];
                    if (isset($validated['name'])) $ownerUpdateData['name'] = $validated['name'];
                    if (isset($validated['email'])) $ownerUpdateData['email'] = $validated['email'];
                    if (array_key_exists('phone', $validated)) $ownerUpdateData['phone'] = $validated['phone'];
                    if (!empty($validated['password'])) {
                        $ownerUpdateData['password'] = bcrypt($validated['password']);
                    }

                    if (!empty($ownerUpdateData)) {
                        $clinic->owner->update($ownerUpdateData);
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث بيانات العيادة والشعار بنجاح',
                'data' => $clinic->fresh(['owner', 'subscription.plan'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء تعديل بيانات العيادة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 7. حذف عيادة بالكامل من المنصة
     */
    public function destroyClinic($id)
    {
        $clinic = Clinic::findOrFail($id);
        
        try {
            DB::transaction(function () use ($clinic) {
                $doctorIds = $clinic->doctors()->pluck('id');

                if ($doctorIds->isNotEmpty()) {
                    Appointment::whereIn('doctor_id', $doctorIds)->delete();
                }

                $clinic->doctors()->delete();
                $clinic->subscription()->delete();
                $clinic->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف العيادة وجميع بياناتها بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء محاولة حذف العيادة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 8. تحديث أو ربط اشتراك عيادة معينة بباكة معينة يدوياً
     */
    public function updateClinicSubscription(Request $request, $clinicId)
    {
        $clinic = Clinic::findOrFail($clinicId);

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:active,expired,suspended,cancelled',
            'expires_at' => 'nullable|date',
        ]);

        $subscription = ClinicSubscription::updateOrCreate(
            ['clinic_id' => $clinic->id],
            [
                'plan_id' => $validated['plan_id'],
                'status' => $validated['status'],
                'expires_at' => $validated['expires_at'] ?? now()->addDays(30),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث اشتراك العيادة بنجاح',
            'data' => $subscription
        ], 200);
    }

    /**
     * 9. عرض جميع مستخدمين المنصة بجميع أدوارهم (أدمن عيادات، أطباء، مرضى)
     */
    public function indexUsers()
    {
        $users = User::with('clinic')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ], 200);
    }

    /**
     * 10. حذف مستخدم من المنصة
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role === 'super_admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن حذف حساب مدير المنصة العام'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف المستخدم بنجاح'
        ], 200);
    }


 /**
     * عرض كافة الاشتراكات
     */
    public function indexSubscriptions()
    {
        $subscriptions = ClinicSubscription::with(['clinic', 'plan'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $subscriptions
        ], 200);
    }

    /**
     * إضافة اشتراك جديد لعيادة
     */

   /**
     * إضافة اشتراك جديد لعيادة
     */
    public function storeSubscription(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|string|in:active,expired,cancelled,trial', // تم التحديث هنا
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $subscription = ClinicSubscription::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الاشتراك بنجاح',
            'data' => $subscription->load(['clinic', 'plan'])
        ], 201);
    }

    /**
     * تعديل اشتراك موجود
     */
    public function updateSubscription(Request $request, $id)
    {
        $subscription = ClinicSubscription::findOrFail($id);

        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|string|in:active,expired,cancelled,trial', // تم التحديث هنا
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $subscription->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الاشتراك بنجاح',
            'data' => $subscription->fresh(['clinic', 'plan'])
        ], 200);
    }

    /**
     * حذف الاشتراك
     */
    public function destroySubscription($id)
    {
        $subscription = ClinicSubscription::findOrFail($id);
        $subscription->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الاشتراك بنجاح'
        ], 200);
    }
}
