<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MasRole;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make(

            $request->all(),

            [
                'name' => 'required|string|max:255',

                'email' => 'required|email|unique:users,email',

                'password' => 'required|min:6',

                'phone_no' => 'required'
            ],

            [
                'name.required' => 'Name is required',

                'email.required' => 'Email is required',

                'email.email' => 'Please enter a valid email',

                'email.unique' => 'User already exists with this email',

                'password.required' => 'Password is required',

                'password.min' => 'Password must be at least 6 characters',

                'phone_no.required' => 'Phone number is required'
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDATION FAILED
        |--------------------------------------------------------------------------
        */

        if ($validator->fails()) {

            return response()->json([

                'success' => false,

                'message' => $validator->errors()->first(),

                'errors' => $validator->errors()

            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | FIND STUDENT ROLE
        |--------------------------------------------------------------------------
        */

        $studentRole = MasRole::where('name', 'student')->first();

        if (!$studentRole) {

            return response()->json([

                'success' => false,

                'message' => 'Student role not found'

            ], 500);
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | CREATE USER
            |--------------------------------------------------------------------------
            */

            $user = User::create([

                'role_id' => $studentRole->id,

                'name' => $request->name,

                'email' => $request->email,

                'password' => Hash::make($request->password),

                'phone_no' => $request->phone_no
            ]);

            /*
            |--------------------------------------------------------------------------
            | AUTO LOGIN
            |--------------------------------------------------------------------------
            */

            Auth::login($user);

            /*
            |--------------------------------------------------------------------------
            | REGENERATE SESSION
            |--------------------------------------------------------------------------
            */

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

            /*
            |--------------------------------------------------------------------------
            | SUCCESS RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                'message' => 'Account created successfully',

                'data' => [

                    'user' => $user,

                    'has_access' => false
                ]

            ], 201);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => 'Registration failed',

                'error' => $e->getMessage()

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make(

            $request->all(),

            [
                'email' => 'required|email',

                'password' => 'required'
            ],

            [
                'email.required' => 'Email is required',

                'email.email' => 'Please enter a valid email',

                'password.required' => 'Password is required'
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDATION FAILED
        |--------------------------------------------------------------------------
        */

        if ($validator->fails()) {

            return response()->json([

                'success' => false,

                'message' => $validator->errors()->first(),

                'errors' => $validator->errors()

            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK LOGIN
        |--------------------------------------------------------------------------
        */

        if (!Auth::attempt([

            'email' => $request->email,

            'password' => $request->password

        ])) {

            return response()->json([

                'success' => false,

                'message' => 'Invalid email or password'

            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | REGENERATE SESSION
        |--------------------------------------------------------------------------
        */

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        /*
        |--------------------------------------------------------------------------
        | GET AUTH USER
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | DEFAULT ACCESS
        |--------------------------------------------------------------------------
        */

        $hasAccess = false;

        /*
        |--------------------------------------------------------------------------
        | ADMIN ALWAYS HAS ACCESS
        |--------------------------------------------------------------------------
        */

        if ($user->role_id == 3) {

            $hasAccess = true;
        }

        /*
        |--------------------------------------------------------------------------
        | FACULTY ALWAYS HAS ACCESS
        |--------------------------------------------------------------------------
        */

        if ($user->role_id == 2) {

            $hasAccess = true;
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT ACCESS CHECK
        |--------------------------------------------------------------------------
        */

        if ($user->role_id == 1) {

            $student = Student::where(

                'user_id',

                $user->id

            )->first();

            if ($student) {

                $hasAccess = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS RESPONSE
        |--------------------------------------------------------------------------
        */

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([

            'success' => true,

            'message' => 'Login successful',

            'data' => [

                'user' => $user,

                'token' => $token,

                'has_access' => $hasAccess
            ]

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
        }

        if ($request->hasSession()) {
            $request->session()->regenerateToken();
        }

        return response()->json([

            'success' => true,

            'message' => 'Logout successful'

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT USER
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        $user = Auth::user();

        if (!$user) {

            return response()->json([

                'success' => false,

                'message' => 'User not authenticated'

            ], 401);
        }

        return response()->json([

            'success' => true,

            'message' => 'Authenticated user fetched successfully',

            'data' => $user

        ], 200);
    }

    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
            
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                $studentRole = MasRole::where('name', 'student')->first();
                if (!$studentRole) {
                    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                    return redirect($frontendUrl . '/login?error=student_role_not_found');
                }

                $user = User::create([
                    'role_id' => $studentRole->id,
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(\Illuminate\Support\Str::random(24)),
                    'phone_no' => null,
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect($frontendUrl . '/live');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth callback failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect($frontendUrl . '/login?error=oauth_failed');
        }
    }
}