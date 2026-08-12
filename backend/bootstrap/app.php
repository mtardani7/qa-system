<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Support\ApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \Spatie\Permission\Middleware\RoleMiddleware::class, 'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class, 'activity' => \App\Http\Middleware\RecordUserActivity::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->is('api/*')) return ApiResponse::error('Validation failed.', $exception->errors(), 422);
        });
        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if ($request->is('api/*')) return ApiResponse::error('You are not authorized to perform this action.', [], 403);
        });
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $exception, Request $request) {
            if ($request->is('api/*')) return ApiResponse::error('You are not authorized to perform this action.', [], 403);
        });
        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            if ($request->is('api/*')) return ApiResponse::error('You are not authorized to perform this action.', [], 403);
        });
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) return ApiResponse::error('Authentication is required.', [], 401);
        });
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*')) return ApiResponse::error('The requested resource was not found.', [], 404);
        });
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if ($request->is('api/*')) return ApiResponse::error('An unexpected server error occurred.', [], 500);
        });
    })->create();
