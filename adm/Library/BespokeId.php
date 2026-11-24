<?php

namespace App\Library;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class BespokeId
{
    public static function redirect(): RedirectResponse
    {
        $state = Str::random(40);
        $code_verifier = Str::random(96);

        session([
            'bespokeid_state'         => $state,
            'bespokeid_code_verifier' => $code_verifier,
        ]);

        $query = http_build_query([
            'client_id'             => config('services.bespokeid.client_id'),
            'redirect_uri'          => config('services.bespokeid.redirect'),
            'response_type'         => 'code',
            'scope'                 => 'profile',
            'state'                 => $state,
            'code_challenge'        => static::codeChallenge($code_verifier),
            'code_challenge_method' => 'S256',
        ]);

        return redirect(static::baseUrl() . '/oauth/authorize?' . $query);
    }

    public static function callback(string $code, ?string $state): array
    {
        $expected_state = session()->pull('bespokeid_state');
        $code_verifier = session()->pull('bespokeid_code_verifier');

        if (!$expected_state || !$state || !hash_equals($expected_state, $state)) {
            throw new RuntimeException('BespokeID login could not be verified (state mismatch). Please try again.');
        }

        $token_response = Http::asForm()->post(static::baseUrl() . '/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.bespokeid.client_id'),
            'client_secret' => config('services.bespokeid.client_secret'),
            'redirect_uri'  => config('services.bespokeid.redirect'),
            'code'          => $code,
            'code_verifier' => $code_verifier,
        ]);

        if ($token_response->failed()) {
            throw new RuntimeException('BespokeID rejected the login attempt: ' . $token_response->body());
        }

        $access_token = $token_response->json('access_token');

        $identity_response = Http::withToken($access_token)
            ->acceptJson()
            ->get(static::baseUrl() . '/api/user');

        if ($identity_response->failed()) {
            throw new RuntimeException('Could not fetch your BespokeID identity: ' . $identity_response->body());
        }

        return $identity_response->json();
    }

    protected static function baseUrl(): string
    {
        return rtrim(config('services.bespokeid.base_url'), '/');
    }

    protected static function codeChallenge(string $code_verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');
    }
}
