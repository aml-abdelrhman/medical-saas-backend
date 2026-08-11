<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
  public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role === 'super_admin') {
            return $next($request);
        }

        return response()->json(['message' => 'غيريحق لك الوصول لهذه الصفحة. صلاحيات مدير المنصة مطلوبة.'], 403);
    }
}