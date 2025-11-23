<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Traits\ApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {

                switch (true) {
                    // Validation Exception
                    case $e instanceof ValidationException:
                        return ApiResponse::errorResponse(
                            'Validation failed',
                            422,
                            $e->errors()
                        );

                    // Authentication error
                    case $e instanceof AuthenticationException:
                        return ApiResponse::errorResponse(
                            'Unauthenticated',
                            401
                        );

                    // Authorization error
                    case $e instanceof AuthorizationException:
                        return ApiResponse::errorResponse(
                            'Forbidden',
                            403
                        );

                    // HTTP exceptions (404, 405, dll)
                    case $e instanceof HttpException:
                        return ApiResponse::errorResponse(
                            $e->getMessage() ?: 'HTTP Error',
                            $e->getStatusCode()
                        );

                    // Fallback untuk semua exception lain
                    default:
                        return ApiResponse::errorResponse(
                            $e->getMessage() ?: 'Server Error',
                            500
                        );
                }
            }

            // Kalau bukan API request, biarkan Laravel default handling
            return null;
        });

    })
    ->create();