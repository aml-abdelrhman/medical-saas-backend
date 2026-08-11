<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClinicSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. إذا لم يكن هناك مستخدم مسجل دخول، دعه يمر لينظمه الـ auth middleware
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. إذا كان المستخدم Super Admin، فهو مستثنى وله صلاحية كاملة على كل شيء
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 3. إذا كان المستخدم يتبع عيادة (مثل Admin العيادة أو طبيب)
        if ($user->clinic_id) {
            $clinic = $user->clinic;

            // التحقق مما إذا كانت العيادة تمتلك اشتراكاً نشطاً أو في فترة التجربة
            $subscription = $clinic ? $clinic->subscription : null;

            if (!$subscription || !$subscription->isActive()) {
                return response()->json([
                    'message' => 'اشتراك العيادة منتهٍ أو غير نشط. يرجى تجديد الاشتراك للتمكن من متابعة العمل.',
                    'error_code' => 'SUBSCRIPTION_EXPIRED'
                ], 403);
            }
        }

        return $next($request);
    }
}