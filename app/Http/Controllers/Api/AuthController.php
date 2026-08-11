<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'phone' => 'nullable|string',
            'role' => 'nullable|in:patient,admin,doctor,super_admin',
            'clinic_id' => 'nullable|exists:clinics,id'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'phone' => $fields['phone'] ?? null,
            'role' => $fields['role'] ?? 'patient',
            'clinic_id' => $fields['clinic_id'] ?? null
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response(['user' => $user->load('clinic'), 'token' => $token], 201);
    }

   public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'clinic_slug' => 'nullable|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        // استثناء السوبر أدمن من قيود العيادة
        if ($user->role !== 'super_admin') {
            if (in_array($user->role, ['admin', 'doctor'])) {
                if (!$user->clinic_id) {
                    return response(['message' => 'هذا الحساب غير مرتبط بأي عيادة'], 403);
                }

                if (!empty($fields['clinic_slug'])) {
                    $clinic = Clinic::where('slug', $fields['clinic_slug'])->first();
                    if (!$clinic || $user->clinic_id !== $clinic->id) {
                        return response(['message' => 'ليس لديك صلاحية الدخول لهذه العيادة'], 403);
                    }
                }
            }
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response(['user' => $user->load('clinic'), 'token' => $token], 200);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('clinic')
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ], 200);
    }
}