<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        \Log::info('Checking user role: ' . $request->user()->role); 
    \Log::info('Required roles: ' . implode(',', $roles));
        // التحقق من أن المستخدم مسجل دخول وله الدور المطلوب في عمود role
        if ($request->user() && in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        return response()->json(['message' => 'غير مصرح لك بالدخول'], 403);
    }
}