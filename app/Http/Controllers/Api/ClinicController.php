<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User; // تأكدي أيضاً من إضافة الـ User Model لو مش موجودة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // <--- أضيفي هذا السطر هنا
use Illuminate\Validation\ValidationException;

class ClinicController extends Controller
{
  public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:clinics,slug',
                'owner_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|unique:clinics,email',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:6',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:12288',
            ]);

            $result = DB::transaction(function () use ($request, $validated) {
                $plainPassword = $validated['password'];

                // 1. تجهيز بيانات العيادة
                $inputName = $validated['name'];
                $encodedName = json_encode([
                    'ar' => $inputName,
                    'en' => $inputName
                ], JSON_UNESCAPED_UNICODE);

                $logoPath = null;
                if ($request->hasFile('logo')) {
                    $file = $request->file('logo');
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/clinics-logos'), $filename);
                    $logoPath = 'uploads/clinics-logos/' . $filename;
                }

                // إدخال البيانات في جدول الـ clinics مع ملء الحقول الإجبارية لتجنب خطأ الـ Database
                $clinic = Clinic::create([
                    'name' => $encodedName,
                    'slug' => $validated['slug'],
                    'owner_name' => $validated['owner_name'], // حفظ اسم المالك في الجدول لتجنب خطأ 1364
                    'email' => $validated['email'],           // إيميل التواصل للعيادة
                    'phone' => $validated['phone'],           // هاتف العيادة
                    'password' => Hash::make($plainPassword), // باسورد مؤقت للعيادة إن وجد في الـ fillable
                    'logo' => $logoPath,
                    'status' => 'active',
                ]);

                // 2. إنشاء حساب المالك في جدول الـ users وربطه بالعيادة عبر clinic_id
                $user = User::create([
                    'name' => $validated['owner_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($plainPassword),
                    'phone' => $validated['phone'],
                    'role' => 'admin',       // مدير العيادة حسب دالة isClinicAdmin
                    'clinic_id' => $clinic->id,
                ]);

                // إنشاء Token لتسجيل الدخول فوراً
                $token = $user->createToken('myapptoken')->plainTextToken;

                return [
                    'clinic' => $clinic->load(['doctors', 'services']),
                    'user' => $user,
                    'token' => $token
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل العيادة وحساب المالك بنجاح',
                'data' => $result['clinic'],
                'user' => $result['user'],
                'token' => $result['token']
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
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    public function show($slug)
    {
        $clinic = Clinic::where('slug', $slug)->with(['specialties', 'doctors', 'services', 'reviews.patient'])->first();

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $clinic->average_rating = $clinic->average_rating;
        $clinic->reviews_count = $clinic->reviews_count;

        return response()->json([
            'status' => true,
            'data' => $clinic
        ], 200);
    }

    public function getDoctors($slug)
    {
        $clinic = Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $doctors = $clinic->doctors()->with('specialty')->get();

        return response()->json([
            'status' => true,
            'data' => $doctors
        ], 200);
    }

    public function getSpecialties($slug)
    {
        $clinic = Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $specialties = $clinic->specialties;

        return response()->json([
            'status' => true,
            'data' => $specialties
        ], 200);
    }

    public function getServices($slug)
    {
        $clinic = Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $services = $clinic->services()->where('is_active', true)->get();

        return response()->json([
            'status' => true,
            'data' => $services
        ], 200);
    }

    public function getPlans()
    {
        try {
            $plans = \App\Models\Plan::all();

            return response()->json([
                'status' => true,
                'message' => 'تم جلب الباقات بنجاح',
                'data' => $plans
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ في الخادم',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
