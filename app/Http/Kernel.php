<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     */
    protected $middleware = [
        // Confianza de proxies
        \App\Http\Middleware\TrustProxies::class,

        // Manejo de CORS (IMPORTANTE para APIs)
        \Illuminate\Http\Middleware\HandleCors::class,

        // Previene requests demasiado grandes
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        // Limpia strings
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,

        // Convierte strings vacíos a null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [

        /*
        |--------------------------------------------------------------------------
        | WEB
        |--------------------------------------------------------------------------
        */
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | API  (CLAVE PARA TU PROBLEMA)
        |--------------------------------------------------------------------------
        */
        'api' => [
            // ❌ NO CSRF en API
            // ❌ NO sesiones
            // ❌ NO cookies

            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     */
    protected $middlewareAliases = [

        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,

        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,

        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
