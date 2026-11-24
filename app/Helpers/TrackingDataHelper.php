<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class TrackingDataHelper
{
    /**
     * Session key used to store tracking data
     * 
     * @var string
     */
    const SESSION_KEY       = 'form-tracking-data';

    /**
     * The tracking data parameters to store
     * 
     * @var array
     */
    protected $tracking_parameters          = [];

    /**
     * Human-friendly labels for the tracking data parameters
     * being stored
     * 
     * @var array
     */
    protected $tracking_parameter_labels    = [];

    /**
     * Any referring platforms we're interested in tracking
     * 
     * @var array
     */
    protected $referring_platforms          = [];

    /**
     * Any referring platforms to associate with paid ads
     * 
     * @var array
     */
    protected $paid_ad_platforms            = [];

    /**
     * Any referring platforms to associate with organic
     * searches
     * 
     * @var array
     */
    protected $organic_search_platforms     = [];

    /**
     * Any referring platforms to associate with AI
     * 
     * @var array
     */
    protected $ai_platforms                 = [];

    /**
     * Any referring platforms to associate with email
     * 
     * @var array
     */
    protected $email_domains                = [];

    /**
     * Any referring platforms to associate with directories
     * 
     * @var array
     */
    protected $directory_domains            = [];

    /**
     * Any referring platforms to associate with Android
     * apps
     * 
     * @var array
     */
    protected $android_app_platforms        = [];

    /**
     * Tracking parameters to check for against the source
     * URL
     * 
     * @var array
     */
    protected $source_track_params          = [];

    /**
     * Tracking parameters to check for against the referring
     * URL
     * 
     * @var array
     */
    protected $referring_track_params       = [];

    /**
     * Construct a new instance of the helper, loading tracking
     * data parameters & settings from configuration files
     */
    public function __construct()
    {
        $this->tracking_parameters          = config('tracking_data.tracking_parameters');
        $this->tracking_parameter_labels    = config('tracking_data.tracking_parameter_labels');
        $this->referring_platforms          = config('tracking_data.referring_platforms');
        $this->paid_ad_platforms            = config('tracking_data.paid_ad_platforms');
        $this->organic_search_platforms     = config('tracking_data.organic_search_platforms');
        $this->ai_platforms                 = config('tracking_data.ai_platforms');
        $this->email_domains                = config('tracking_data.email_domains');
        $this->directory_domains            = config('tracking_data.directory_domains');
        $this->android_app_platforms        = config('tracking_data.android_app_platforms');
    }

    /**
     * Source & set any tracking data from the current page &
     * referring URLs
     * 
     * @return void
     */
    public function sourceAndSetData(): void
    {
        $source_url = (string)request()->input('page_url');
        $referring_url = (string)request()->input('referrer') ?: null;
        $referring_domain = parse_url((string)request()->input('referrer'), PHP_URL_HOST) ?: null;
        $referring_scheme = parse_url((string)request()->input('referrer'), PHP_URL_SCHEME) ?: null;

        $tracking_data = [
            'Entry URL'         => $source_url
        ];
        if (!empty($referring_url)) {
            $tracking_data['Referring URL'] = $referring_url;
            $tracking_data['Referring source'] = $this->getReferringSource($referring_url, $referring_domain, $referring_scheme);
        }

        if (!$this->shouldSetData($source_url, $referring_url, $referring_domain)) {
            // Only replace basic data if not already stored
            if (!request()->session()->has(self::SESSION_KEY)) {
                $this->updateSession($tracking_data);
            }
            return;
        }

        // Otherwise, refresh the tracked UTM/etc data.
        $track_data = !empty($this->source_track_params) ? $this->source_track_params : $this->referring_track_params;
        foreach ($track_data as $track_param => $track_value) {
            $tracking_data[$this->tracking_parameter_labels[$track_param]] = $track_value;
        }

        $this->updateSession($tracking_data);
    }

    /**
     * Get any stored tracking data
     * 
     * @return array
     */
    public function getTrackingData(): array
    {
        if (!request()->session()->has(self::SESSION_KEY)) {
            return [];
        }

        $tracking_data = request()->session()->get(self::SESSION_KEY);
        if (empty($tracking_data) || !Str::isJson($tracking_data)) {
            return [];
        }

        return (array)json_decode($tracking_data, true);
    }

    /**
     * Get the referring source to store from the provided
     * referring URL
     * 
     * @param string $referring_url
     * @return ?string
     */
    protected function getReferringSource(string $referring_url, string $referring_domain, ?string $referring_scheme): ?string
    {
        if (empty($referring_url)) { 
            return $this->checkGenericPaidTerms((string)request()->input('page_url')); 
        }

        if (!is_null($internal = $this->checkInternal($referring_domain))) {
            return $internal;
        }

        if ($referring_scheme === 'android-app') {
            return $this->checkAndroidApp($referring_domain);
        }

        if (!is_null($platform = $this->matchPlatform($referring_domain, $this->referring_platforms))) {
            return $platform;
        }
        if (!is_null($platform = $this->matchPlatform($referring_domain, $this->ai_platforms))) {
            return "AI referral ({$platform})";
        }
        if (!is_null($this->matchPlatform($referring_domain, $this->email_domains))) {
            return "Email";
        }
        if (!is_null($this->matchPlatform($referring_domain, $this->directory_domains))) {
            return "Directory";
        }
        if (!is_null($engine = $this->checkSearchEngines($referring_url, $referring_domain))) {
            return $engine;
        }
        if (!is_null($paid = $this->checkGenericPaidTerms($referring_url))) {
            return $paid;
        }

        return "Referral ({$referring_domain})";
    }

    /**
     * Check if the provided URL contains any paid search
     * tracking parameters, returning the platform if so
     * otherwise null
     * 
     * @param string $url
     * @return string|null
     */
    protected function checkGenericPaidTerms(string $url): ?string
    {
        $params = $this->parseTrackingQueryString($url);
        foreach ($this->paid_ad_platforms as $platform_source => $platform_params) {
            foreach ($platform_params as $param) {
                if (isset($params[$param])) {
                    return 'Paid search - ' . $platform_source;
                }
            }
        }

        return null;
    }

    /**
     * Check if the provided URL is internal traffic,
     * returning "Internal" if so otherwise null
     * 
     * @param string $domain
     * @return string|null
     */
    protected function checkInternal(string $domain): ?string
    {
        $self = parse_url((string)config('app.url'), PHP_URL_HOST);
        if (empty($self)) {
            return null;
        }

        return $this->hostMatches($domain, [$self]) ? 'Internal' : null;
    }

    /**
     * Check if the provided URL & domain correspond to
     * a search engine, returning the engine & organic/paid
     * status if so, otherwise null
     * 
     * @param string $url
     * @param string $domain
     * @return string|null
     */
    protected function checkSearchEngines(string $url, string $domain): ?string
    {
        $engine = $this->matchPlatform($domain, $this->organic_search_platforms);
        if (is_null($engine)) {
            return null;
        }

        return is_null($this->checkGenericPaidTerms($url)) ? "Organic search ({$engine})" : "Paid search ({$engine})";
    }

    /**
     * Check if the referral came from an android app link,
     * returning the app type if so otherwise null
     * 
     * @param string $domain
     * @return string|null
     */
    protected function checkAndroidApp(string $domain): ?string
    {
        return data_get($this->android_app_platforms, $domain);
    }

    /**
     * Determine whether any tracking data should be set, based 
     * on the presense of any configured parameters
     * 
     * @param string $source_url
     * @param string $referring_url
     * @return bool
     */
    protected function shouldSetData(string $source_url, string $referring_url, string $referring_domain): bool
    {
        if ($this->checkInternal($referring_domain)) {
            return false;
        }

        $this->source_track_params = $this->parseTrackingQueryString($source_url);
        $this->referring_track_params = $this->parseTrackingQueryString($referring_url);

        return !empty($this->source_track_params) || !empty($this->referring_track_params);
    }

    /**
     * Parse the query string of the provided URL, checking
     * for any configured tracking parameters & returning
     * the compiled set of data
     * 
     * @param string $url
     * @return array
     */
    protected function parseTrackingQueryString(string $url): array
    {
        $tracking_qs_params = [];

        $qs = parse_url($url, PHP_URL_QUERY);
        if (!empty($qs)) {
            $qs_parts = explode('&', $qs);
            foreach ($qs_parts as $part) {
                $part_parts = explode('=', $part);
                $param = $part_parts[0];
                if (!in_array($param, $this->tracking_parameters)) { continue; }

                $tracking_qs_params[$param] = isset($part_parts[1]) ? $part_parts[1] : null;
            }
        }

        return $tracking_qs_params;
    }

    /**
     * Update the tracking data session
     * 
     * @param array $tracking_data
     * @return void
     */
    protected function updateSession(array $tracking_data): void
    {
        request()->session()->put(self::SESSION_KEY, $tracking_data);
        request()->session()->save();
    }

    /**
     * Match the provided domain against the provided
     * platforms - returning the platform if a match
     * is found otherwise null
     * 
     * @param string $domain
     * @param array $platforms
     * @return string|null
     */
    protected function matchPlatform(string $domain, array $platforms): ?string
    {
        foreach ($platforms as $platform_source => $platform_domains) {
            if ($this->hostMatches($domain, $platform_domains)) {
                return $platform_source;
            }
        }

        return null;
    }

    /**
     * Determine if the provided host matches any of
     * the provided domains
     * 
     * @param string $host
     * @param array $domains
     * @return bool
     */
    protected function hostMatches(string $host, array $domains): bool
    {
        if (empty($host)) { 
            return false;
        }

        foreach ($domains as $domain) {
            if (strcasecmp($host, $domain) === 0 || stripos($host, '.' . $domain) === strlen($host) - strlen('.' . $domain)) {
                return true;
            }
        }

        return false;
    }
}