<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('sanctum')->user() && $request->user()->tokenCan('user')) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. User access only.'], 403);
    }
}
