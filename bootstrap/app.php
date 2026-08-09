<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Derrière un proxy (Traefik chez Dokploy, ou tout autre reverse
        // proxy), la requête arrive en HTTP depuis le réseau interne. Sans
        // cette confiance, Laravel croit le visiteur en clair : il génère de
        // mauvaises URLs, peut boucler sur les redirections HTTPS et
        // journalise l'IP du proxy au lieu de celle du client — ce qui
        // fausserait aussi la limitation de débit sur /login et /webhooks.
        //
        // On ne fait confiance qu'aux réseaux privés, et non à '*' : un
        // en-tête X-Forwarded-For forgé depuis Internet ne sera jamais cru.
        $middleware->trustProxies(
            at: array_map('trim', explode(',', (string) env(
                'TRUSTED_PROXIES',
                '10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,127.0.0.1'
            ))),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        // Les webhooks des fournisseurs de paiement sont signés côté
        // serveur (confirmation API), pas par jeton CSRF
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
