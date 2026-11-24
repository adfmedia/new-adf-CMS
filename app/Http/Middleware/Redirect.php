<?php

namespace App\Http\Middleware;

use App\Models\Redirect as RedirectModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Redirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $url_path = (string)$request->uri()->path();
        $Redirected = RedirectModel::beforeUrlLookup()->search($url_path)->first();
        // If no direct redirection, check for a wildcard
        if (!$Redirected) {
            $Redirected = RedirectModel::beforeUrlLookup()->wildcardSearch($url_path)->first();
        }

        if ($Redirected) {
            return response()->redirectTo(
                RedirectModel::parseRedirectTo($url_path, $Redirected),
                $Redirected->redirect_type
            );
        }

        return $next($request);
    }
}