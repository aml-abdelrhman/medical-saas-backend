<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Service;
use Symfony\Component\HttpFoundation\Response;

class EnsureServiceOwnership
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. جلب الخدمة من المسار (الـ ID الذي يرسله المستخدم في الرابط)
        $serviceId = $request->route('service'); 
        $service = Service::find($serviceId);

        // 2. التحقق من وجود الخدمة
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // 3. التحقق: إذا كان المستخدم طبيباً، هل الخدمة مملوكة له؟
        // نفترض أن الطبيب مسجل دخوله ولديه علاقة 'doctor' مع الـ User
        if (auth()->check() && auth()->user()->role === 'doctor') {
            if ($service->doctor_id !== auth()->user()->doctor->id) {
                return response()->json(['message' => 'Unauthorized: You do not own this service'], 403);
            }
        }

        // 4. إذا كان أدمن أو الطبيب صاحب الخدمة، أكمل الطلب
        return $next($request);
    }
}