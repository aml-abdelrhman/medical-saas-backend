<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * جلب قائمة المستخدمين التابعين للعيادة فقط
     */
    public function index(Request $request)
    {
        try {
            // استقبال معرف العيادة إما من الـ Query Parameters أو من المستخدم المُسجل حالياً
            $clinicId = $request->input('clinic_id') ?? $request->user()->clinic_id;

            $users = User::when($clinicId, function ($query, $clinicId) {
                return $query->where('clinic_id', $clinicId);
            })->get();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء جلب المستخدمين'], 500);
        }
    }

    /**
     * تحديث دور المستخدم (Role) مع التأكد أنه يتبع نفس العيادة للأمان
     */
    public function updateRole(Request $request, $id)
    {
        try {
            $request->validate([
                'role' => 'required|in:patient,doctor,admin',
            ]);

            $clinicId = $request->input('clinic_id') ?? $request->user()->clinic_id;

            // البحث عن المستخدم بشرط أن يكون ينتمي لنفس العيادة لضمان الحماية
            $user = User::when($clinicId, function ($query, $clinicId) {
                return $query->where('clinic_id', $clinicId);
            })->findOrFail($id);

            $user->update([
                'role' => $request->role
            ]);

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث دور المستخدم بنجاح',
                'user' => $user
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'تعذر تحديث الدور: ' . $e->getMessage()], 500);
        }
    }

    /**
     * حذف مستخدم تابع للعيادة
     */
    public function destroy(Request $request, $id)
    {
        try {
            $clinicId = $request->input('clinic_id') ?? $request->user()->clinic_id;

            $user = User::when($clinicId, function ($query, $clinicId) {
                return $query->where('clinic_id', $clinicId);
            })->findOrFail($id);

            $user->appointments()->delete(); 
            $user->favorites()->delete();
            $user->delete();
            
            return response()->json(['status' => true, 'message' => 'تم الحذف بنجاح'], 200);
        } catch (\Exception $e) {
            \Log::error("Error deleting user: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * إضافة مستخدم جديد للعيادة
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'required|in:patient,doctor,admin',
            ]);

            $clinicId = $request->user()->clinic_id;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role,
                'clinic_id' => $clinicId, // ربط المستخدم بالعيادة تلقائياً
            ]);

            return response()->json([
                'status' => true,
                'message' => 'تمت إضافة المستخدم بنجاح',
                'user' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء إضافة المستخدم'], 500);
        }
    }
}