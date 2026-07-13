<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL HTTP MIDDLEWARE
    |--------------------------------------------------------------------------
    */

    protected $middleware = [

        \App\Http\Middleware\TrustProxies::class,

        \Illuminate\Http\Middleware\HandleCors::class,

        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,

        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        \App\Http\Middleware\TrimStrings::class,

        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | MIDDLEWARE GROUPS
    |--------------------------------------------------------------------------
    */

    protected $middlewareGroups = [

        'web' => [

            \App\Http\Middleware\EncryptCookies::class,

            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

            \Illuminate\Session\Middleware\StartSession::class,

            \Illuminate\View\Middleware\ShareErrorsFromSession::class,

            \App\Http\Middleware\VerifyCsrfToken::class,

            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,

            'throttle:api',

            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | ROUTE MIDDLEWARE
    |--------------------------------------------------------------------------
    */

    protected $routeMiddleware = [

        /*
        |--------------------------------------------------------------------------
        | DEFAULT LARAVEL MIDDLEWARE
        |--------------------------------------------------------------------------
        */

        'auth' => \App\Http\Middleware\Authenticate::class,

        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,

       'auth.session.api' => \App\Http\Middleware\EnsureUserIsAuthenticated::class,

        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,

        'can' => \Illuminate\Auth\Middleware\Authorize::class,

        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,

        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,

        'signed' => \App\Http\Middleware\ValidateSignature::class,

        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        /*
        |--------------------------------------------------------------------------
        | CUSTOM ROLE MIDDLEWARE
        |--------------------------------------------------------------------------
        */

        'isStudent' => \App\Http\Middleware\IsStudent::class,

        'isFaculty' => \App\Http\Middleware\IsFaculty::class,

        'isAdmin' => \App\Http\Middleware\IsAdmin::class,
        'hasAccess' => \App\Http\Middleware\HasAccess::class,
    ];
}