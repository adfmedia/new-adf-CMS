<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Lines to include in the outputted robots file
     * 
     * @var array
     */
    protected $lines    = [
        '#Created via RobotsController'
    ];

    /**
     * Serve the robots.txt file
     * 
     * @return Response
     */
    public function index(): Response
    {
        switch (config('app.env')) {
            case 'production':
                return $this->live();
            default:
                return $this->notLive();
        }

        return response('');
    }

    /**
     * Serve the live-site configured robots.txt file
     * 
     * @return Response
     */
    protected function live(): Response
    {
        $this->lines[] = 'Sitemap: ' . route('sitemap.xml');
        $this->lines[] = '';
        $this->lines[] = 'User-Agent: *';
        $this->lines[] = 'Allow: /';
        $this->lines[] = 'Disallow: /adm';
        $this->lines[] = 'Disallow: /ajax';

        return $this->serveLinesResponse();
    }

    /**
     * Serve the not-live-site configured robots.txt file
     * 
     * @return Response
     */
    protected function notLive(): Response
    {
        $this->lines[] = 'User-Agent: *';
        $this->lines[] = 'Disallow: /';

        return $this->serveLinesResponse();
    }

    /**
     * Serve the defined robots.txt lines as a plain-text
     * response
     * 
     * @return Response
     */
    protected function serveLinesResponse(): Response
    {
        return response(implode(PHP_EOL, $this->lines))
            ->header('Content-type', 'text/plain');
    }
}