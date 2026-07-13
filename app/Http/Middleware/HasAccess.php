<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Student;

class HasAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /*
        |--------------------------------------------------------------------------
        | CHECK AUTH USER
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | USER NOT LOGGED IN
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            return response()->json([

                'success' => false,

                'message' => 'Unauthenticated'

            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN ALWAYS HAS ACCESS
        |--------------------------------------------------------------------------
        */

        if ($user->role_id == 3) {

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | FACULTY ALWAYS HAS ACCESS
        |--------------------------------------------------------------------------
        */

        if ($user->role_id == 2) {

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK STUDENT ACCESS
        |--------------------------------------------------------------------------
        */

        $student = Student::where('user_id', $user->id)

            ->first();

        /*
        |--------------------------------------------------------------------------
        | ACCESS DENIEDx
        |--------------------------------------------------------------------------
        */

        if (!$student) {

            return response()->json([

                'success' => false,

                'message' => 'Please complete enrollment approval first'

            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | ACCESS GRANTED
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}