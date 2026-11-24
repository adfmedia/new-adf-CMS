<?php

namespace Adm\Library\BingWebmaster;

use Illuminate\Support\Facades\Log;

class IndexNow
{
    /**
     * Submits the provided URL to Bing Webmaster Tools'
     * IndexNow feature
     * 
     * @param string $url
     * @return void
     */
    public static function submitUrl(string $url): void
    {
        $API_KEY = config('services.bing_webmaster.index_now.api_key');
        if (empty($API_KEY)) { return; }

        $keyLocation = config('services.bing_webmaster.index_now.key_location');
        if (!$keyLocation) {
            $keyLocation = rtrim(route('theme'), '/') . '/' . $API_KEY . '.txt';
        }
        $payload = [
            'host'          => parse_url(config('app.url'), PHP_URL_HOST),
            'key'           => $API_KEY,
            'keyLocation'   => $keyLocation,
            'urlList'       => [$url]
        ];

        $ch = curl_init('https://api.indexnow.org/IndexNow');
        curl_setopt_array($ch, [
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => json_encode($payload),
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => [
                'Content-Type: application/json; charset=utf-8'
            ]
        ]);
        $resp = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if (config('services.bing_webmaster.index_now.log_calls')) {
            Log::debug('-------- Bing IndexNow --------');
            Log::debug('Sent:');
            Log::debug(json_encode($payload));
            Log::debug('Response:');
            Log::debug($resp);
            Log::debug('Info:');
            Log::debug(json_encode($info));
            Log::debug('-------- END Bing IndexNow --------');
        }
    }
}