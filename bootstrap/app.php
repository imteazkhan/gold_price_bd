<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'track.visit' => \App\Http\Middleware\TrackVisit::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
        // visitor cookie only holds a random UUID; no need to encrypt it
        $middleware->encryptCookies(except: [\App\Http\Middleware\TrackVisit::COOKIE]);
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
