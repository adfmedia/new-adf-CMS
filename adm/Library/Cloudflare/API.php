<?php

namespace Adm\Library\Cloudflare;

use Illuminate\Support\Facades\Log;
use StdClass;

class API
{
    /**
     * cURL timeout value
     * 
     * @var int
     */
    const TIMEOUT       = 5;

    /**
     * cURL base URL
     * 
     * @var string
     */
    const URL           = 'https://api.cloudflare.com/client/v4/';

    /**
     * Constant representing GET requests
     * 
     * @var int
     */
    const METHOD_GET    = 0;

    /**
     * Constant representing POST requests
     * 
     * @var int
     */
    const METHOD_POST   = 1;

    /**
     * Constant representing PATCH requests
     * 
     * @var int
     */
    const METHOD_PATCH  = 2;

    /**
     * Constant representing DELETE requests
     * 
     * @var int
     */
    const METHOD_DELETE = 3;

    /**
     * Authentication email for the Cloudflare API
     * 
     * @var string
     */
    protected $auth_email   = '';

    /**
     * Authentication key for the Cloudflare API
     * 
     * @var string
     */
    protected $auth_key     = '';

    /**
     * Construct a new API wrapper instance with authentication
     * details
     * 
     * @param string $auth_email    The email address to authenticate with
     * @param string $auth_key      The API key to authenticate with
     */
    public function __construct(string $auth_email, string $auth_key)
    {
        $this->auth_email = $auth_email;
        $this->auth_key = $auth_key;
    }

    /**
     * Purge all URL paths for the provided domain
     * 
     * @param string $domain    The domain to purge URLs for
     * @return \StdClass|bool
     */
    public function purgeSite(string $domain): StdClass|bool
    {
        $identifier = $this->getDomainIdentifier($domain);
        if (!$identifier) { return false; }

        return $this->http_request(
            'zones/' . $identifier . '/purge_cache',
            self::METHOD_DELETE,
            [
                'purge_everything'  => true
            ]
        );
    }

    /**
     * Purge specific URLs for the provided domain
     * 
     * @param string $domain    The domain to purge URLs for
     * @param array $urls       The URL paths to purge
     * @return \StdClass|bool
     */
    public function purgeUrls(string $domain, array $urls = []): StdClass|bool
    {
        $identifier = $this->getDomainIdentifier($domain);
        if (!$identifier) { return false; }

        return $this->http_request(
            'zones/' . $identifier . '/purge_cache',
            self::METHOD_DELETE,
            [
                'files' => $urls
            ]
        );
    }

    /**
     * Get the development mode flag for the provided domain
     * 
     * @param string $domain    The domain to get the flag for
     * @return \StdClass|bool
     */
    public function getDevelopmentMode(string $domain): StdClass|bool
    {
        $identifier = $this->getDomainIdentifier($domain);
        if (!$identifier) { return false; }

        return $this->http_request(
            'zones/' . $identifier . '/settings/development_mode',
            self::METHOD_GET
        );
    }

    /**
     * Set development mode for a domain to the provided value
     * 
     * @param string $domain    The domain to set the flag for
     * @param string $value     The development mode value to set
     * @return \StdClass|bool
     */
    public function setDevelopmentMode(string $domain, string $value): StdClass|bool
    {
        $identifier = $this->getDomainIdentifier($domain);
        if (!$identifier) { return false; }

        return $this->http_request(
            'zones/' . $identifier . '/settings/development_mode',
            self::METHOD_PATCH,
            [
                'value' => $value
            ]
        );
    }

    /**
     * Get the API identifier for the provided domain
     * 
     * @param string $domain    The domain to get the identifier for
     * @return string|bool
     */
    protected function getDomainIdentifier(string $domain): string|bool
    {
        $Zone = $this->getZone($domain);
        if (isset($Zone->result) && count($Zone->result) == 1) {
            return $Zone->result[0]->id;
        }

        return false;
    }

    /**
     * Return the Cloudflare zone for the provided name
     * 
     * @param string $name  The domain name to get a zone for
     * @return \StdClass
     */
    protected function getZone(string $name): StdClass
    {
        return $this->http_request(
            'zones',
            self::METHOD_GET,
            [
                'name'      => $name,
                'status'    => 'active',
                'page'      => 1,
                'match'     => 'all'
            ]
        );
    }

    /**
     * Perform a HTTP request against the API
     * 
     * @param string $endpoint  The API endpoint to call
     * @param string $method    The HTTP request method to use
     * @param array $data       The request data to send
     * @return \StdClass
     */
    protected function http_request(string $endpoint, string $method, array $data = []): StdClass
    {
        $url = self::URL . $endpoint;
        if ($method == self::METHOD_GET && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $curl_options = [
            CURLOPT_URL             => $url,
            CURLOPT_TIMEOUT         => self::TIMEOUT,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => [
                "X-Auth-Email: {$this->auth_email}",
                "X-Auth-Key: {$this->auth_key}",
                "Content-Type: application/json"
            ]
        ];

        if ($method == self::METHOD_POST) {
            $curl_options[CURLOPT_POST] = true;
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        elseif ($method == self::METHOD_PATCH) {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        elseif ($method == self::METHOD_DELETE) {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $resp = curl_exec($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp_code != 200) {
            Log::error('Cloudflare API Error - response below');
            Log::error($resp);
        }

        return json_decode($resp);
    }
}