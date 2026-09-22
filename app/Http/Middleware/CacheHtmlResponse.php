<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheHtmlResponse
{
    /**
     * Give cacheable public HTML pages a short, revalidated cache lifetime
     * instead of the framework's "no-cache, private" default. Skipped whenever
     * the response carries session-specific state (flash messages, a just-issued
     * registration ticket) so nothing personalized gets cached.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $isCacheableHtml = $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && str_starts_with($response->headers->get('Content-Type', ''), 'text/html')
            && !$request->session()->has('success')
            && !$request->session()->has('registration_ticket');

        if ($isCacheableHtml) {
            $response->headers->set('Cache-Control', 'public, max-age=300, must-revalidate');
        }

        return $response;
    }
}
