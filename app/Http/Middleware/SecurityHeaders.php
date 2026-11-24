<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  ?string  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $guard = null)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        if (substr(config('app.url'), 0, 8) == 'https://') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000;');
        }

        return $response;
    }
}