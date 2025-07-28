<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Sanctum\PersonalAccessToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->append(ForceJsonResponse::class);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.token.expiration' => \App\Http\Middleware\CheckTokenExpiration::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // custom handler for MethodNotAllowedHttpException
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'Method Not Allowed'
            ], 405);
        });

        // custom handler for NotFoundHttpException
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'Not Found'
            ], 404);
        });

        // custom handler for RouteNotFoundException
        $exceptions->render(function (Symfony\Component\Routing\Exception\RouteNotFoundException  $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Route'
            ], 401);
        });

        // custom handler for AccessDeniedHttpException
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException  $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Access'
            ], 401);
        });

        // custom handler for AuthenticationException
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                  'status' => false,
                  'message' => 'Unauthenticated.'
                ], 401);
            }
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        PersonalAccessToken::where('expires_at', '<', now())->delete();
    })->create();
