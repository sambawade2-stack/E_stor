<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP appliquée uniquement en production : le serveur de dev Vite
        // (npm run dev) charge son client HMR depuis une autre origine et
        // ouvre un WebSocket, ce qu'une CSP stricte casserait en local.
        // 'unsafe-eval' est requis par Alpine.js (build standard, évalue les
        // expressions x-data/x-on via new Function()) ; 'unsafe-inline' sur
        // style-src par les quelques style="--d: …" utilisés pour les délais
        // d'animation. Le reste est restreint à l'origine du site.
        if (app()->isProduction()) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
                "font-src 'self' https://fonts.bunny.net",
                "img-src 'self' data:",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "frame-ancestors 'self'",
                "form-action 'self'",
            ]));
        }

        return $response;
    }
}
