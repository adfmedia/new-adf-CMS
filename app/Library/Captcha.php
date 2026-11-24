<?php

namespace App\Library;

use Illuminate\Support\Str;

class Captcha 
{
    /**
     * Verify the provided v3 $captcha_response, returning an array of:
     *  - The success status
     *  - The captcha score
     *  - The full response content, as an array
     * 
     * @param mixed $captcha_response
     * @return array
     */
    public static function verify(mixed $captcha_response): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             => 'https://www.google.com/recaptcha/api/siteverify',
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => [
                'secret'    => config('services.recaptcha.secret_key'),
                'response'  => $captcha_response
            ],
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_SSL_VERIFYPEER  => false
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if (!Str::isJson($resp)) {
            return [false, 0, []];
        }

        $resp_arr = json_decode($resp, true);
        $success = isset($resp_arr['success']) ? $resp_arr['success'] : false;
        $score = isset($resp_arr['score']) ? $resp_arr['score'] : 0;
        return [$success, $score, $resp_arr];
    }

    /**
     * Validates the score returned from recaptcha, returning
     * true if above the configured threshold, otherwise
     * false
     * 
     * @param mixed $score
     * @return bool
     */
    public static function validateScore(mixed $score): bool
    {
        $score = (float)$score;
        $threshold = (float)config('services.recaptcha.min_score');
        return $score >= $threshold;
    }
}