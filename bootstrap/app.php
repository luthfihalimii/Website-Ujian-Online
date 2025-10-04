<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'student' => \App\Http\Middleware\AuthStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($request->header('X-Inertia')) {
                $homeUrl = Route::has('student.dashboard') ? route('student.dashboard') : url('/');
                $adminUrl = Route::has('admin.dashboard') ? route('admin.dashboard') : null;

                return Inertia::render('Errors/NotFound', [
                    'status' => 404,
                    'homeUrl' => $homeUrl,
                    'adminUrl' => $adminUrl,
                ])->toResponse($request)->setStatusCode(404);
            }

            return null;
        });
    })->create();
