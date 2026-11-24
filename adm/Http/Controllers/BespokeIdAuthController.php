<?php

namespace Adm\Http\Controllers;

use App\Library\BespokeId;
use Adm\Models\AdmUser;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BespokeIdAuthController extends AdmController
{
    /**
     * Sends the browser to BespokeID to begin the login
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        return BespokeId::redirect();
    }

    /**
     * Handles the return trip from BespokeID: verifies the login, finds
     * the matching CMS account, and signs it in
     *
     * @return RedirectResponse
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $identity = BespokeId::callback($request->query('code', ''), $request->query('state'));
        } catch (\Throwable $e) {
            Log::warning('BespokeID login failed', ['exception' => $e->getMessage()]);

            return redirect()->route('adm.login')->withErrors([
                'email' => 'BespokeID sign-in failed or was cancelled. Please try again.',
            ]);
        }

        if (empty($identity['id']) || empty($identity['email'])) {
            return redirect()->route('adm.login')->withErrors([
                'email' => 'BespokeID did not return a valid identity. Please try again.',
            ]);
        }

        $AdmUser = AdmUser::where('email', config('services.bespokeid.admin_account_email'))->first();
        if (!$AdmUser) {
            return redirect()->route('adm.login')->withErrors([
                'email' => 'No CMS account exists for this BespokeID identity. Contact an administrator.',
            ]);
        }

        Auth::guard('adm')->login($AdmUser);

        // Shuffle password to prevent future logins
        $AdmUser->forceFill([
            'password' => Str::random(32),
        ]);
        $AdmUser->save();

        $request->session()->regenerate();

        if (config('auth.mfa.adm.enabled')) {
            if (!$AdmUser->mfa_enrolled) {
                return response()->redirectTo(route('adm.mfa-enrol'));
            }
            return response()->redirectTo(route('adm.mfa-prompt'));
        }

        return response()->redirectTo(route('adm.route'));
    }
}
