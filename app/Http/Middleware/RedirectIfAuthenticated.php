<?php

namespace App\Http\Middleware;

use App\Models\Url;
use Closure;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated as BaseMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                switch ($guard) {
                    case 'adm':
                        return redirect(route('adm.login'));
                    default:
                        $Route = Route::current();
                        if ($Route && $Route->getPrefix() == '/adm') {
                            return $next($request);
                        }

                        return redirect($this->redirectTo($request));
                }
            }
        }

        return $next($request);
    }

    /**
     * Get the default URI the user should be redirected to when they are authenticated.
     */
    protected function defaultRedirectUri(): string
    {
        return self::getLoginRedirectUri();
    }

    /**
     * Get the default URI the user should be redirected to when they are authenticated.
     */
    public static function getLoginRedirectUri(): string
    {
        $ProfilePage = Url::getAccountPage();
        return $ProfilePage && $ProfilePage->url_slug ? '/' . $ProfilePage->url_slug : '/';
    }
}