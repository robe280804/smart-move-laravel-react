<?php

use App\Enums\SecurityEventType;
use App\Events\SecurityAlert;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);
        $middleware->append(SecurityHeadersMiddleware::class);
        $middleware->alias([
            'ability' => CheckForAnyAbility::class,
            'role' => RoleMiddleware::class,
            'verified' => EnsureEmailIsVerified::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (HttpException $e) {
            if ($e->getStatusCode() === 403) {
                event(new SecurityAlert(
                    type: SecurityEventType::ForbiddenAccess,
                    ip: request()->ip() ?? 'unknown',
                    userId: request()->user()?->id,
                    details: "403 Forbidden: {$e->getMessage()} | URL: ".request()->fullUrl(),
                ));
            }
        });

        $exceptions->reportable(function (Throwable $e) {
            if ($e instanceof HttpException) {
                return;
            }

            event(new SecurityAlert(
                type: SecurityEventType::UnhandledException,
                ip: request()->ip() ?? 'unknown',
                userId: request()->user()?->id,
                details: get_class($e).': '.$e->getMessage(),
            ));
        });
    })->create();
