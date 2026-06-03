<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DEMO SHIM ONLY. In production you put Cloudflare in front and it sets the real
 * `CF-IPCountry` header per request — laravel-rebel-core then records it on the audit
 * trail automatically (see rebel-core.geo). On localhost there's no Cloudflare, so
 * this middleware injects a country header when one is absent, purely so the demo's
 * Audit Explorer shows the country column working. Delete this in a real app.
 */
final class SetDemoCountryHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->headers->has('CF-IPCountry')) {
            $request->headers->set('CF-IPCountry', (string) env('DEMO_COUNTRY', 'IT'));
        }

        return $next($request);
    }
}
