<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Notsoweb\ApiResponse\Enums\ApiResponse;
use Notsoweb\LaravelCore\Http\APIException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('api', [
            StartSession::class,
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ServiceUnavailableHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::SERVICE_UNAVAILABLE->response();
            }
        });
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::UNPROCESSABLE_CONTENT->response([
                    'message' => $e->getMessage()
                ]);
            }
        });
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::UNPROCESSABLE_CONTENT->response([
                    'message' => __($e->getMessage())
                ]);
            }
        });
        $exceptions->render(APIException::notFound(...));
        $exceptions->render(APIException::unauthorized(...));
        $exceptions->render(APIException::unprocessableContent(...));
    })->create();
