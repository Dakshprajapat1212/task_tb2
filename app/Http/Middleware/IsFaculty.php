<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsFaculty
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {

            return response()->json([

                'success' => false,

                'message' => 'Unauthenticated'

            ], 401);
        }

        if (Auth::user()->role_id != 2) {

            return response()->json([

                'success' => false,

                'message' => 'Faculty access only'

            ], 403);
        }

        return $next($request);
    }
}