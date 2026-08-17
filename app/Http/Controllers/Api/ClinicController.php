<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClinicController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'       => 'required|string|max:255',
                'slug'       => 'required|string|max:255|unique:clinics,slug',
                'owner_name' => 'required|string|max:255',
                'email'      => 'required|email|unique:users,email|unique:clinics,email',
                'phone'      => 'required|string|max:20',
                'password'   => 'required|string|min:6',
                'logo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:12288',
            ]);
$result = DB::transaction(function () use ($request, $validated) {
    $plainPassword = $validated['password'];

    $logoPath = null;
    // رفع شعار العيادة عبر Cloudinary باستخدام Storage Disk الموحد
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('clinics', 'cloudinary');
        $uploadedFileUrl = Storage::disk('cloudinary')->url($path);
        
        $logoPath = $uploadedFileUrl;
        Log::info('=== CLINIC STORE: CLOUDINARY LOGO UPLOADED ===', ['url' => $uploadedFileUrl]);
    }

    $clinic = Clinic::create([
        'name'       => $validated['name'],   // ← string عادي زي ما هو، من غير json_encode
        'slug'       => $validated['slug'],
        'owner_name' => $validated['owner_name'],
        'email'      => $validated['email'],
        'phone'      => $validated['phone'],
        'password'   => Hash::make($plainPassword),
        'logo'       => $logoPath,
        'status'     => 'active',
    ]);

    $user = User::create([
        'name'      => $validated['owner_name'],
        'email'     => $validated['email'],
        'password'  => Hash::make($plainPassword),
        'phone'     => $validated['phone'],
        'role'      => 'admin',
        'clinic_id' => $clinic->id,
    ]);

    $token = $user->createToken('myapptoken')->plainTextToken;

    return [
        'clinic' => $clinic->load(['doctors', 'services']),
        'user'   => $user,
        'token'  => $token
    ];
});

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل العيادة وحساب المالك بنجاح',
                'data'    => $result['clinic'],
                'user'    => $result['user'],
                'token'   => $result['token']
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشل التحقق من البيانات المدخلة',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
    
    public function show($slug)
    {
        $clinic = Clinic::where('slug', $slug)
            ->with(['specialties', 'doctors', 'services', 'reviews.patient'])
            ->first();

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $clinicData = $clinic->toArray();
        $clinicData['name'] = is_string($clinic->name) ? (json_decode($clinic->name, true) ?? $clinic->name) : $clinic->name;
        $clinicData['average_rating'] = $clinic->average_rating;
        $clinicData['reviews_count'] = $clinic->reviews_count;

        return response()->json([
            'status' => true,
            'data'   => $clinicData
        ], 200);
    }

    public function getDoctors($slug)
    {
        $clinic = Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $doctors = $clinic->doctors()->with('specialty')->get()->map(function ($doctor) {
            $doctorArray = $doctor->toArray();
            $doctorArray['name'] = is_string($doctor->name) ? (json_decode($doctor->name, true) ?? $doctor->name) : $doctor->name;
            if (isset($doctorArray['specialty']) && is_array($doctorArray['specialty'])) {
                $specName = $doctor->specialty->name ?? null;
                $doctorArray['specialty']['name'] = is_string($specName) ? (json_decode($specName, true) ?? $specName) : $specName;
            }
            return $doctorArray;
        });

        return response()->json([
            'status' => true,
            'data'   => $doctors
        ], 200);
    }

    public function getSpecialties($slug)
    {
        $clinic = Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $specialties = $clinic->specialties->map(function ($specialty) {
            return [
                'id'          => $specialty->id,
                'clinic_id'   => $specialty->clinic_id,
                'name'        => is_string($specialty->name) ? (json_decode($specialty->name, true) ?? $specialty->name) : $specialty->name,
                'description' => is_string($specialty->description) ? (json_decode($specialty->description, true) ?? $specialty->description) : $specialty->description,
                'slug'        => $specialty->slug
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $specialties
        ], 200);
    }

    public function getServices($slug)
    {
        $clinic = Clinic::where('slug', $slug)->first();

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'العيادة غير موجودة'
            ], 404);
        }

        $services = $clinic->services()->where('is_active', true)->get()->map(function ($service) {
            $serviceArray = $service->toArray();
            $serviceArray['name'] = is_string($service->name) ? (json_decode($service->name, true) ?? $service->name) : $service->name;
            return $serviceArray;
        });

        return response()->json([
            'status' => true,
            'data'   => $services
        ], 200);
    }

    public function getPlans()
    {
        try {
            $plans = \App\Models\Plan::all();

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب الباقات بنجاح',
                'data'    => $plans
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ في الخادم',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}