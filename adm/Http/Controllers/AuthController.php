<?php

namespace Adm\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Adm\Http\Controllers\AdmController;
use Adm\Models\AdmUser;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordValidation;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Writer as QRWriter;
use BaconQrCode\Renderer\ImageRenderer as QRRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle as QRRendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd as QRRendererImage;
use Illuminate\Http\JsonResponse;

class AuthController extends AdmController
{
    /**
     * Shows the CMS login page
     * 
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Performs CMS login
     * 
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(): RedirectResponse
    {
        $this->validate(
            request(),
            [
                'email'     => ['required', 'string', 'email'],
                'password'  => ['required', 'string']
            ]
        );

        if (!Auth::guard('adm')->attempt(request()->only('email', 'password'), request()->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email'       => trans('auth.failed'),
                'credentials' => true
            ]);
        }

        request()->session()->regenerate();

        if (config('auth.mfa.adm.enabled')) {
            $User = Auth::guard('adm')->user();
            if (!$User->mfa_enrolled) {
                return response()->redirectTo(route('adm.mfa-enrol'));
            }
            return response()->redirectTo(route('adm.mfa-prompt'));
        }

        return response()->redirectTo(route('adm.route'));
    }

    /**
     * Shows the forgot password form & sends out reset
     * emails
     * 
     * @return \Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function forgot_password(): View
    {
        $success = false;
        $invalid_user = false;
        if (request()->isMethod('post')) {
            $this->validate(
                request(),
                [
                    'email' => ['required', 'email']
                ]
            );

            $status = Password::broker('adm_users')->sendResetLink(request()->only('email'));
            if ($status == Password::RESET_LINK_SENT) {
                $success = true;
            }
            elseif($status == Password::INVALID_USER) {
                $invalid_user = true;
            }
        }

        return view(
            'auth.forgot-password',
            [
                'success'   => $success,
                'invalid_user'     => $invalid_user
            ]
        );
    }

    /**
     * Shows the reset password form & resets user passwords
     * 
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function reset_password(string $token): View|RedirectResponse
    {
        if (request()->isMethod('post')) {
            $this->validate(
                request(),
                [
                    'token'     => 'required',
                    'email'     => ['required', 'email'],
                    'password'  => ['required', 'confirmed', PasswordValidation::defaults()]
                ]
            );

            $request = request();
            $status = Password::broker('adm_users')->reset(
                [
                    'email'                 => $request->input('email'),
                    'password'              => $request->input('password'),
                    'password_confirmation' => $request->input('password_confirmation'),
                    'token'                 => $request->input('token')
                ],
                function ($user) use ($request) {
                    $user->forceFill([
                        'password'          => Hash::make($request->password),
                        'remember_token'    => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            return $status == Password::PASSWORD_RESET
                ? redirect()->route('adm.login')->with('status', __($status))
                : back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
        }

        return view(
            'auth.reset-password',
            [
                'request'   => request()
            ]
        );
    }

    public function mfa_enrol(): View|RedirectResponse|JsonResponse
    {
        if (request()->isMethod('post')) {
            $User = AdmUser::find(request()->input('user_id'));
            if (!$User) {
                return response()->json([
                    'success'   => false,
                    'error'     => 'Sorry, something went wrong. Please try again later.'
                ]);
            }
            if ($User->mfa_enrolled) {
                return response()->json([
                    'success'   => false,
                    'error'     => 'You are already enrolled, redirecting to login.',
                    'redirect'  => route('adm.login')
                ]);
            }

            $MFA = new Google2FA();
            if (!$MFA->verifyKey(request()->input('secret'), request()->input('verify'))) {
                return response()->json([
                    'success'   => false,
                    'error'     => 'Invalid code.'
                ]);
            }

            $User->fill([
                'mfa_enrolled'  => true,
                'mfa_secret'    => request()->input('secret')
            ]);
            $User->save();

            Auth::guard('adm')->login($User);
            return response()->json([
                'success'   => true,
                'redirect'  => route('adm.mfa-prompt')
            ]);
        }

        $User = Auth::guard('adm')->user();
        if (!$User) {
            return response()->redirectTo(route('adm.login'));
        }

        $MFA = new Google2FA();
        $secret = $MFA->generateSecretKey();
        $QRWriter = new QRWriter(new QRRenderer(new QRRendererStyle(300), new QRRendererImage()));
        $qrUrl = $MFA->getQRCodeUrl(config('app.name'), $User->email, $secret);
        $qrSvg = $QRWriter->writeString($qrUrl);

        Auth::guard('adm')->logout();

        return view('auth.mfa-enrol', [
            'user_id'   => $User->id,
            'secret'    => $secret,
            'qr_svg'    => $qrSvg
        ]);
    }

    public function mfa_prompt(): View|RedirectResponse|JsonResponse
    {
        if (request()->isMethod('post')) {
            $User = AdmUser::find(request()->input('user_id'));
            if (!$User) {
                return response()->json([
                    'success'   => false,
                    'error'     => 'Sorry, something went wrong. Please try again later.'
                ]);
            }
            if (!$User->mfa_enrolled) {
                return response()->json([
                    'success'   => false,
                    'error'     => 'You are not enrolled, redirecting to login.',
                    'redirect'  => route('adm.login')
                ]);
            }

            $MFA = new Google2FA();
            if (!$MFA->verifyKey($User->mfa_secret, request()->input('verify'))) {
                return response()->json([
                    'success'   => false,
                    'error'     => 'Invalid code.'
                ]);
            }

            Auth::guard('adm')->login($User);

            return response()->json([
                'success'   => true,
                'redirect'  => route('adm.route')
            ]);
        }

        $User = Auth::guard('adm')->user();
        if (!$User) {
            return response()->redirectTo(route('adm.login'));
        }

        Auth::guard('adm')->logout();

        return view('auth.mfa-prompt', [
            'user_id'   => $User->id
        ]);
    }

    /**
     * Logs out of the CMS
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        Auth::guard('adm')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->redirectTo(route('adm.login'));
    }
}