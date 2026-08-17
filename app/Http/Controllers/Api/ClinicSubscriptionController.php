<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicSubscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClinicSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'plan_id' => 'required|exists:plans,id',
            'is_annual' => 'boolean',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        
        $durationInDays = $plan->duration_in_days ?? 30;
        if ($request->boolean('is_annual')) {
            $durationInDays = 365;
        }

        $startsAt = Carbon::now();
        $endsAt = (clone $startsAt)->addDays($durationInDays);

        ClinicSubscription::where('clinic_id', $validated['clinic_id'])
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $subscription = ClinicSubscription::create([
            'clinic_id' => $validated['clinic_id'],
            'plan_id' => $validated['plan_id'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'active', 
        ]);

        // جلب رابط الفرونت إند من ملف الـ .env مع وضع رابط Vercel الخاص بكِ كقيمة افتراضية آمنة
        $frontendUrl = env('APP_FRONTEND_URL', 'https://medical-saas-frontend-5yoa.vercel.app');
        $paymentUrl = rtrim($frontendUrl, '/') . '/mock-checkout?subscription_id=' . $subscription->id;

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء طلب الاشتراك بنجاح',
            'data' => [
                'subscription' => $subscription->load('plan'),
                'payment_url' => $paymentUrl,
            ]
        ], 201);
    }
}