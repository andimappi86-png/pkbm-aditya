<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enforce HTTPS redirect if force_https is enabled in production and request is not secure
        if (app()->environment('production') && (bool) config('app.force_https', false) && ! $request->isSecure()) {
            return redirect()->secure($request->getRequestUri());
        }

        /** @var Response $response */
        $response = $next($request);

        // Core security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        if (! $response->headers->has('X-Permitted-Cross-Domain-Policies')) {
            $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        }

        if (! $response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        // Content-Security-Policy
        if (! $response->headers->has('Content-Security-Policy')) {
            $isProduction = app()->environment('production');

            $connectSrc = $isProduction
                ? "'self'"
                : "'self' ws: wss: http://localhost:* http://127.0.0.1:*";

            $cspDirectives = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com",
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com",
                "font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com",
                "img-src 'self' data: blob: https:",
                "media-src 'self'",
                "frame-src 'self' https://www.youtube.com https://youtube.com https://www.youtube-nocookie.com",
                "frame-ancestors 'self'",
                "connect-src {$connectSrc}",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ];

            $response->headers->set('Content-Security-Policy', implode('; ', $cspDirectives));
        }

        // Strict-Transport-Security (HSTS)
        // Strictly only applied in production on HTTPS connections; never on localhost or plain HTTP.
        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
