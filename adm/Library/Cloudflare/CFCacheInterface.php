<?php

namespace Adm\Library\Cloudflare;

use Illuminate\Support\Facades\Cache;

class CFCacheInterface
{
    /**
     * Boolean flag determining if the API is enabled or not
     * 
     * @var bool
     */
    protected $enabled  = false;

    /**
     * The email address used to login & authenticate with
     * 
     * @var ?string
     */
    protected $login_email  = null;

    /**
     * The API key used to authenticate with
     * 
     * @var ?string
     */
    protected $api_key      = null;

    /**
     * The domain to target with API calls
     * 
     * @var ?string
     */
    protected $domain       = null;

    /**
     * Local instance of the Cloudflare API wrapper
     * 
     * @var ?\Adm\Library\Cloudflare\API
     */
    protected $API          = null;

    /**
     * Construct a new API interface, authenticating
     * with config / ENV variable settings
     */
    public function __construct()
    {
        $this->login_email = config('services.cloudflare.email');
        $this->api_key = config('services.cloudflare.api_key');
        $this->domain = config('services.cloudflare.domain');

        if (!empty($this->login_email) && !empty($this->api_key) && !empty($this->domain)) {
            $this->enabled = true;
        }
    }

    /**
     * Return the API enabled flag
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Purge all URLs for the domain
     * 
     * @return void
     */
    public function purgeAll(): void
    {
        if (!$this->enabled) { return; }

        $this->getAPI()->purgeSite($this->domain);
    }

    /**
     * Purge specific URLs for the domain
     * 
     * @param array $urls   The URL paths to purge
     * @return void
     */
    public function purgeUrls(array $urls = []): void
    {
        if (!$this->enabled) { return; }

        $this->getAPI()->purgeURLs($this->domain, $urls);
    }

    /**
     * Return 'on' or 'off' depending on the current
     * Cloudflare development mode flag
     * 
     * @return string
     */
    public function getDevelopmentMode(): string
    {
        if (!$this->enabled) { return false; }

        return Cache::remember(
            'cms-cf-dev-mode',
            (60 * 60),  // 1 hour in seconds
            function() {
                return $this->isDevelopmentModeOn() ? 'on' : 'off';
            }
        );
    }

    /**
     * Switch development mode flag between on and off
     * 
     * @return void
     */
    public function switchDevelopmentMode(): void
    {
        if (!$this->enabled) { return; }

        if ($this->isDevelopmentModeOn()) {
            $switch_to = 'off';
        } else {
            $switch_to = 'on';
        }

        $this->getAPI()->setDevelopmentMode($this->domain, $switch_to);
        Cache::forget('cms-cf-dev-mode');
    }

    /**
     * Returns a boolean representing if development mode
     * is on or not
     * 
     * @return bool
     */
    protected function isDevelopmentModeOn(): bool
    {
        $Resp = $this->getAPI()->getDevelopmentMode($this->domain);
        return $Resp && $Resp->success ? $Resp->result->value == 'on' : false;
    }

    /**
     * Create a new API instance if needed, store against
     * the local property & return it
     * 
     * @return \Adm\Library\Cloudflare\API
     */
    protected function getAPI(): API
    {
        if (is_null($this->API)) {
            $this->API = new API($this->login_email, $this->api_key);
        }

        return $this->API;
    }
}