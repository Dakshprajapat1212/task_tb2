<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * AUTHENTICATION MIDDLEWARE
 * ============================================================================
 * 
 * This middleware protects routes by ensuring only authenticated users can
 * access them. It's used to create "protected routes" that require login.
 * 
 * WHAT IS MIDDLEWARE?
 * - Middleware acts as a bridge between a request and a response
 * - It runs BEFORE the request reaches the controller
 * - Can modify the request, response, or block the request entirely
 * - Think of it as a security checkpoint before your code runs
 * 
 * HOW THIS MIDDLEWARE WORKS:
 * 1. When a request comes to a protected route, this middleware runs first
 * 2. It checks if the user is authenticated using Auth::check()
 * 3. Auth::check() looks at the session cookie sent by the browser
 * 4. If session exists and contains a valid user ID, user is authenticated
 * 5. If authenticated, request proceeds to the controller
 * 6. If not authenticated, returns 401 Unauthorized response
 * 
 * FLOW DIAGRAM:
 * 
 * Browser Request (with session cookie)
 *         ↓
 *    Middleware runs
 *         ↓
 *    Auth::check() reads session cookie
 *         ↓
 *    Session exists & valid?
 *         ├─ YES → Allow request → Controller executes → Response
 *         └─ NO → Block request → Return 401 error
 * 
 * REAL-WORLD PRODUCTION FLOW:
 * 
 * 1. Frontend → Login API
 *    - User sends email/password
 *    - Server validates and creates session
 *    - Server sends session cookie to browser
 * 
 * 2. Session → Cookie
 *    - Browser stores cookie automatically
 *    - Cookie contains session ID (not user data)
 *    - Cookie is HttpOnly (can't be accessed by JavaScript)
 * 
 * 3. Cookie → Protected APIs
 *    - Browser automatically sends cookie with each request
 *    - Middleware reads cookie and validates session
 *    - If valid, user can access protected resources
 * 
 * EXAMPLE USE CASES:
 * - User profile pages (only user can see their own profile)
 * - Dashboard pages (only logged-in users)
 * - API endpoints that modify data (create, update, delete)
 * - Admin panels (only admin users)
 */
class EnsureUserIsAuthenticated
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
        // Check if user is authenticated
        // Auth::check() returns true if user is logged in
        // It works by:
        // 1. Reading the session cookie from the request
        // 2. Looking up the session data on the server
        // 3. Checking if session contains a valid user ID
        // 4. Loading the user from database if needed
        if (!Auth::check()) {
            // User is not authenticated
            // Return 401 Unauthorized response
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login to access this resource.',
            ], 401);
        }

        // User is authenticated
        // Allow request to proceed to the controller
        // The authenticated user will be available via Auth::user()
        return $next($request);
    }
}