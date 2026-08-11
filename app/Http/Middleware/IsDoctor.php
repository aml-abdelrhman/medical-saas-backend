<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsDoctor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
  public function handle(Request $request, Closure $next)
    {
        // تأكدي أن المستخدم مسجل دخوله ولديه دور طبيب
        if ($request->user() && $request->user()->role === 'doctor') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
