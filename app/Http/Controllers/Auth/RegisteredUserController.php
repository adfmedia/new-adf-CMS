<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AuthPageHelper;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * 
     * @return View
     */
    public function create(): View
    {
        list($Page, $UrlObject) = AuthPageHelper::getPageAndUrlObject(
            'auth', 'register', 'register',
            'Register', 'Register for an account', 'Register'
        );

        return view(
            'auth.register',
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
     * Handle an incoming registration request.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RedirectIfAuthenticated::getLoginRedirectUri());
    }
}
