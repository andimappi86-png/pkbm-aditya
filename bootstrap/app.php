<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            $isForbidden = $e instanceof AccessDeniedHttpException ||
                           $e instanceof AuthorizationException ||
                           $e instanceof UnauthorizedException ||
                           ($e instanceof HttpException && $e->getStatusCode() === 403);

            if ($isForbidden) {
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json(['message' => 'Anda tidak memiliki akses ke fitur tersebut.'], 403);
                }

                if (auth()->check()) {
                    if ($request->routeIs('reports.*', 'announcements.show', 'ppdb.show', 'rapor.show', 'rekap.show', 'materi.show', 'forum.show')) {
                        return response('Anda tidak memiliki akses ke fitur tersebut.', 403);
                    }

                    return redirect()->route(auth()->user()->dashboardRoute())
                        ->with('error', 'Anda tidak memiliki akses ke fitur tersebut.');
                }

                return redirect()->route('login')->with('error', 'Anda tidak memiliki akses ke fitur tersebut.');
            }
        });
    })->create();
