<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsStudent
{
    public function handle(Request $request, Closure $next)
    {
        /*
        |--------------------------------------------------------------------------
        | CHECK LOGIN
        |--------------------------------------------------------------------------
        */

        if (!Auth::check()) {

            return response()->json([

                'success' => false,

                'message' => 'Unauthenticated'

            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK ROLE
        |--------------------------------------------------------------------------
        */

        if (Auth::user()->role_id != 1) {

            return response()->json([

                'success' => false,

                'message' => 'Student access only'

            ], 403);
        }

        return $next($request);
    }
}