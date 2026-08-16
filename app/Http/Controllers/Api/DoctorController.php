<?php

namespace App\Http\Controllers\Api;

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
    public function index(Request $request)
    {
        $query = Doctor::query()->with('specialty');

        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        if ($request->has('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        $doctors = $query->get()->map(function ($doctor) {
            return $this->formatDoctor($doctor, false);
        });

        return response()->json($doctors);
    }

    public function show($id)
    {
        $doctor = Doctor::with(['specialty', 'services', 'availabilities'])->findOrFail($id);
        return response()->json($this->formatDoctor($doctor, true));
    }

    public function myProfile(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'doctor') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $doctor = Doctor::with(['specialty', 'services', 'availabilities'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $this->formatDoctor($doctor, true)
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name_ar'          => 'required|string|max:255',
            'name_en'          => 'required|string|max:255',
            'specialty_id'     => 'required|exists:specialties,id',
            'clinic_id'        => 'nullable|exists:clinics,id',
            'bio_ar'           => 'nullable|string',
            'bio_en'           => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'required|numeric|min:0',
            'languages'        => 'nullable',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
        ]);

        $clinicId = $validated['clinic_id'] ?? ($user ? $user->clinic_id : null);

        DB::beginTransaction();
        try {
            $newUser = User::create([
                'name'      => $validated['name_en'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'doctor',
                'clinic_id' => $clinicId,
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('doctors', 'cloudinary');
                $imagePath = Storage::disk('cloudinary')->url($path);
                Log::info('=== DOCTOR STORE: CLOUDINARY IMAGE UPLOADED ===', ['url' => $imagePath]);
            }

            $doctorData = [
                'user_id'          => $newUser->id,
                'clinic_id'        => $clinicId,
                'slug'             => Str::slug($validated['name_en']) . '-' . uniqid(),
                'specialty_id'     => $validated['specialty_id'],
                'name'             => ['ar' => $validated['name_ar'], 'en' => $validated['name_en']],
                'bio'              => ['ar' => $validated['bio_ar'] ?? '', 'en' => $validated['bio_en'] ?? ''],
                'years_experience' => $validated['years_experience'] ?? 0,
                'price_from'       => $validated['price_from'],
                'rating'           => $validated['rating'] ?? 5,
                'image'            => $imagePath,
                'languages'        => is_string($request->input('languages')) ? json_decode($request->input('languages'), true) : ($request->input('languages') ?? []),
            ];

            $doctor = Doctor::create($doctorData);
            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Doctor created successfully',
                'data'    => $this->formatDoctor($doctor->fresh(), true),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== DOCTOR STORE ERROR ===', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create doctor: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $user   = auth()->user();

        if ($user && $user->role === 'doctor' && $doctor->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name_ar'          => 'sometimes|string|max:255',
            'name_en'          => 'sometimes|string|max:255',
            'specialty_id'     => 'sometimes|exists:specialties,id',
            'clinic_id'        => 'sometimes|nullable|exists:clinics,id',
            'bio_ar'           => 'nullable|string',
            'bio_en'           => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'sometimes|numeric|min:0',
            'languages'        => 'nullable',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'email'            => 'sometimes|email|unique:users,email,' . $doctor->user_id,
            'password'         => 'sometimes|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $doctorData = [];
            
            // تحديث الاسم والـ Slug
            if (isset($validated['name_ar']) || isset($validated['name_en'])) {
                $currentName = is_array($doctor->name) ? $doctor->name : (json_decode($doctor->name, true) ?? ['ar' => '', 'en' => '']);
                $doctorData['name'] = [
                    'ar' => $validated['name_ar'] ?? ($currentName['ar'] ?? ''),
                    'en' => $validated['name_en'] ?? ($currentName['en'] ?? ''),
                ];
                if (isset($validated['name_en'])) {
                    $doctorData['slug'] = Str::slug($validated['name_en']) . '-' . $doctor->id;
                }
            }

            // تحديث النبذة
            if ($request->has('bio_ar') || $request->has('bio_en')) {
                $currentBio = is_array($doctor->bio) ? $doctor->bio : (json_decode($doctor->bio, true) ?? ['ar' => '', 'en' => '']);
                $doctorData['bio'] = [
                    'ar' => $request->input('bio_ar', $currentBio['ar'] ?? ''),
                    'en' => $request->input('bio_en', $currentBio['en'] ?? ''),
                ];
            }

            foreach (['specialty_id', 'years_experience', 'price_from', 'rating', 'clinic_id'] as $field) {
                if (isset($validated[$field])) $doctorData[$field] = $validated[$field];
            }

            if ($request->filled('languages')) {
                $doctorData['languages'] = is_string($request->input('languages')) 
                    ? json_decode($request->input('languages'), true) 
                    : $request->input('languages');
            }

            // تحديث الصورة عبر Cloudinary باستخدام Storage Disk الموحد
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('doctors', 'cloudinary');
                $doctorData['image'] = Storage::disk('cloudinary')->url($path);
                Log::info('=== DOCTOR UPDATE: CLOUDINARY IMAGE UPLOADED ===', ['url' => $doctorData['image']]);
            }

            if (!empty($doctorData)) $doctor->update($doctorData);

            // تحديث بيانات المستخدم المرتبط
            if (isset($validated['email']) || isset($validated['password']) || isset($validated['clinic_id'])) {
                $userUpdateData = [];
                if (isset($validated['email'])) $userUpdateData['email'] = $validated['email'];
                if (isset($validated['password'])) $userUpdateData['password'] = Hash::make($validated['password']);
                if (isset($validated['clinic_id'])) $userUpdateData['clinic_id'] = $validated['clinic_id'];
                User::where('id', $doctor->user_id)->update($userUpdateData);
            }

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Doctor updated successfully',
                'data'    => $this->formatDoctor($doctor->fresh(), true),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== DOCTOR UPDATE ERROR ===', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    private function formatDoctor($doctor, $fullDetails = false)
    {
        return [
            'id'               => $doctor->id,
            'user_id'          => $doctor->user_id,
            'clinic_id'        => $doctor->clinic_id,
            'specialty_id'     => $doctor->specialty_id,
            'name'             => is_array($doctor->name) ? $doctor->name : (json_decode($doctor->name, true) ?? $doctor->name),
            'bio'              => is_array($doctor->bio) ? $doctor->bio : (json_decode($doctor->bio, true) ?? $doctor->bio),
            'years_experience' => (int) $doctor->years_experience,
            'rating'           => (float) $doctor->rating,
            'languages'        => is_array($doctor->languages) ? $doctor->languages : (json_decode($doctor->languages, true) ?? []),
            'price_from'       => (float) $doctor->price_from,
            'image'            => $doctor->image, 
            'specialty'        => $doctor->specialty,
            'services'         => $fullDetails ? $doctor->services : null,
            'availabilities'   => $fullDetails ? $doctor->availabilities : null,
        ];
    }
}