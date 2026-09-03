<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * VictoriaBank merchant requirement ("HTTPS encryption... entire website")
 * and the compliance checklist's #11. Guarded by APP_ENV so local/staging
 * dev over plain HTTP keeps working unchanged — this only bites in production.
 * The actual certificate/TLS termination is a hosting concern outside this
 * repo; this only makes sure a stray HTTP request gets redirected, and that
 * Laravel generates https:// URLs when running behind a TLS-terminating proxy.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');

            if (!$request->secure()) {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }
}
