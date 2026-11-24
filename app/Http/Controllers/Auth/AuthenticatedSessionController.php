<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AuthPageHelper;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     * 
     * @return View
     */
    public function create(): View
    {
        list($Page, $UrlObject) = AuthPageHelper::getPageAndUrlObject(
            'auth', 'login', 'login',
            'Login', 'Login to your account', 'Login'
        );

        return view(
            'auth.login',
            [
                'Page'          => $Page,
                'UrlObject'     => $UrlObject,
                'breadcrumbs'   => [
                    ['url' => route('theme'), 'text' => 'Home'],
                    ['url' => route('theme', $UrlObject->url), 'text' => $Page->h1_title]
                ]
            ]
        );
    }

    /**
     * Handle an incoming authentication request.
     * 
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->to(RedirectIfAuthenticated::getLoginRedirectUri());
    }

    /**
     * Destroy an authenticated session.
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
