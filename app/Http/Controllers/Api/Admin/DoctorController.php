<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    /**
     * عرض أطباء العيادة الحالية فقط
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $clinicId = $request->input('clinic_id') ?? ($user?->clinic_id ?? $user?->clinic?->id);

        $query = Doctor::with(['specialty', 'user']);

        if ($clinicId && optional($user)->role === 'super_admin') {
            $query->where('doctors.clinic_id', $clinicId);
        } elseif ($clinicId) {
            $query->where('doctors.clinic_id', $clinicId);
        }

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->input('specialty_id'));
        }

        $doctors = $query->get()->map(function ($doctor) {
            return $this->formatDoctor($doctor, true);
        });

        return response()->json(['success' => true, 'data' => $doctors]);
    }

    /**
     * إضافة طبيب جديد (من لوحة تحكم الأدمن للعيادة) وينشئ له حساب في جدول المستخدمين ورفع الصورة عبر Cloudinary
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar'          => 'required|string|max:255',
            'name_en'          => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'specialty_id'     => 'required|exists:specialties,id',
            'clinic_id'        => 'nullable|exists:clinics,id',
            'bio'              => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'required|numeric|min:0',
            'languages'        => 'nullable',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'image'            => 'nullable|image|max:5120',
        ]);

        $user = $request->user();
        $clinicId = $validated['clinic_id'] ?? ($user?->clinic_id ?? $user?->clinic?->id);

        if (!$clinicId) {
            return response()->json(['success' => false, 'message' => 'رقم العيادة غير متوفر'], 422);
        }

        DB::beginTransaction();

        try {
            // 1. إنشاء الحساب في جدول users أولاً
            $newUser = User::create([
                'name'      => $validated['name_en'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'doctor',
                'clinic_id' => $clinicId,
            ]);

            // 2. إنشاء السجل في جدول doctors وربطه بـ user_id و clinic_id
            $doctorData = [
                'user_id'          => $newUser->id,
                'clinic_id'        => $clinicId,
                'slug'             => Str::slug($validated['name_en']) . '-' . uniqid(),
                'name'             => json_encode([
                    'ar' => $validated['name_ar'],
                    'en' => $validated['name_en'],
                ], JSON_UNESCAPED_UNICODE),
                'specialty_id'     => $validated['specialty_id'],
                'bio'              => $request->filled('bio') ? $request->input('bio') : null,
                'years_experience' => $validated['years_experience'] ?? 0,
                'price_from'       => $validated['price_from'],
                'languages'        => $request->filled('languages') 
                                    ? (is_string($request->input('languages')) ? json_decode($request->input('languages'), true) : $request->input('languages')) 
                                    : [],
                'rating'           => $validated['rating'] ?? 5,
            ];

            // رفع الصورة مباشرة إلى Cloudinary باستخدام Storage Disk
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('doctors', 'cloudinary');
                $uploadedFileUrl = Storage::disk('cloudinary')->url($path);
                
                $doctorData['image'] = $uploadedFileUrl;
                Log::info('=== ADMIN DOCTOR STORE: CLOUDINARY IMAGE UPLOADED ===', ['url' => $uploadedFileUrl]);
            }

            $doctor = Doctor::create($doctorData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطبيب بنجاح',
                'data'    => $this->formatDoctor($doctor->load('user', 'specialty'), true),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Doctor Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطأ أثناء إنشاء الطبيب', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث بيانات طبيب
     */
    public function update(Request $request, $id)
    {
        $doctor = Doctor::with('user')->findOrFail($id);
        $user   = $request->user();

        if (optional($user)->role === 'doctor' && $doctor->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا الملف الشخصي',
            ], 403);
        }

        $validated = $request->validate([
            'name_ar'          => 'sometimes|string|max:255',
            'name_en'          => 'sometimes|string|max:255',
            'specialty_id'     => 'sometimes|exists:specialties,id',
            'clinic_id'        => 'sometimes|exists:clinics,id',
            'bio'              => 'nullable',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'sometimes|numeric|min:0',
            'languages'        => 'nullable',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'email'            => 'sometimes|email|unique:users,email,' . $doctor->user_id,
            'password'         => 'sometimes|string|min:6',
            'image'            => 'nullable|image|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $doctorData = [];

            if (isset($validated['name_ar']) || isset($validated['name_en'])) {
                $currentName = is_array($doctor->name) ? $doctor->name : (json_decode($doctor->name, true) ?? []);
                $newNameAr = $validated['name_ar'] ?? ($currentName['ar'] ?? '');
                $newNameEn = $validated['name_en'] ?? ($currentName['en'] ?? '');

                $doctorData['name'] = json_encode([
                    'ar' => $newNameAr,
                    'en' => $newNameEn,
                ], JSON_UNESCAPED_UNICODE);

                if (isset($validated['name_en'])) {
                    $doctorData['slug'] = Str::slug($newNameEn) . '-' . $doctor->id;
                }
            }

            if ($request->has('bio')) {
                $doctorData['bio'] = $request->input('bio');
            }

            foreach (['specialty_id', 'clinic_id', 'years_experience', 'price_from', 'rating'] as $field) {
                if (isset($validated[$field])) {
                    $doctorData[$field] = $validated[$field];
                }
            }

            if ($request->filled('languages')) {
                $doctorData['languages'] = is_string($request->input('languages'))
                    ? json_decode($request->input('languages'), true)
                    : $request->input('languages');
            }

            // رفع الصورة الجديدة عبر Cloudinary باستخدام Storage Disk
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('doctors', 'cloudinary');
                $uploadedFileUrl = Storage::disk('cloudinary')->url($path);
                
                $doctorData['image'] = $uploadedFileUrl;
                Log::info('=== ADMIN DOCTOR UPDATE: NEW CLOUDINARY IMAGE ===', ['url' => $uploadedFileUrl]);
            }

            if (!empty($doctorData)) {
                $doctor->update($doctorData);
            }

            if (isset($validated['email']) || isset($validated['password'])) {
                $userUpdateData = [];
                if (isset($validated['email'])) {
                    $userUpdateData['email'] = $validated['email'];
                }
                if (isset($validated['password'])) {
                    $userUpdateData['password'] = Hash::make($validated['password']);
                }
                User::where('id', $doctor->user_id)->update($userUpdateData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات الطبيب بنجاح',
                'data'    => $this->formatDoctor($doctor->fresh()->load('user', 'specialty'), true),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Doctor Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطأ أثناء التحديث', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * حذف طبيب وحسابه المرتبط
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $doctor = Doctor::findOrFail($id);
            $userId = $doctor->user_id;

            // الصور على Cloudinary لا تحتاج إلى حذف محلي
            $doctor->delete();

            if ($userId) {
                User::where('id', $userId)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الطبيب وحسابه بنجاح',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Doctor Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطأ أثناء الحذف', 'error' => $e->getMessage()], 500);
        }
    }

    private function formatDoctor($doctor, $full = false)
    {
        $decodedName = is_string($doctor->name) ? (json_decode($doctor->name, true) ?? $doctor->name) : $doctor->name;

        $data = [
            'id'               => $doctor->id,
            'user_id'          => $doctor->user_id,
            'clinic_id'        => $doctor->clinic_id ?? null,
            'name'             => $decodedName,
            'image'            => $doctor->image, // رابط Cloudinary المباشر
            'specialty_id'     => $doctor->specialty_id,
            'specialty'        => $doctor->specialty,
            'user'             => $doctor->user,
            'years_experience' => (int) $doctor->years_experience,
            'price_from'       => (float) $doctor->price_from,
            'rating'           => (float) $doctor->rating,
        ];

        if ($full) {
            $data['bio']            = $doctor->bio;
            $data['languages']      = is_string($doctor->languages) ? json_decode($doctor->languages, true) : $doctor->languages;
            $data['services']       = $doctor->services ?? [];
            $data['availabilities'] = $doctor->availabilities ?? [];
        }

        return $data;
    }
}