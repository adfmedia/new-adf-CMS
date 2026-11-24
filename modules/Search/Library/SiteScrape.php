<?php

namespace Modules\Search\Library;

use App\Helpers\LanguageHelper;
use App\Http\Controllers\RouteController;
use App\Models\Scopes\LanguageScope;
use App\Models\Url;
use DOMDocument;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Search\Models\SearchIndex;

class SiteScrape
{
    /**
     * The console command that called the scrape.
     * 
     * @var Command
     */
    protected $ConsoleCommand;

    /**
     * Local instance of the route controller, used to
     * get URL page content
     * 
     * @var RouteController
     */
    protected $RouteController;

    /**
     * Local variable denoting whether a scrape is currently
     * occurring, used as a factor in app/adm cast decisions
     * 
     * @var bool
     */
    protected static $_IS_SCRAPING  = false;

    /**
     * Local variable storing whether a re-crawl is forced
     * or not. When set to true, a URL will be reindexed
     * regardless of it's last crawl date
     * 
     * @var bool
     */
    protected $recrawl_forced   = false;

    /**
     * Build a new instance, with an optional console command,
     * and return the instance
     */
    public function __construct(?Command $Command)
    {
        $this->ConsoleCommand = $Command;
        $this->RouteController = new RouteController();

        return $this;
    }

    /**
     * Call to indicate the scrape is a forced crawl,
     * reindexing all known URLs
     * 
     * @return void
     */
    public function forceRecrawl(): void
    {
        $this->recrawl_forced = true;
    }

    /**
     * Scrape the site, storing page content in the
     * index table
     * 
     * @return void
     */
    public function scrapeSite(): void
    {
        self::$_IS_SCRAPING = true;

        $UrlsToScrape = Url::withoutGlobalScope(LanguageScope::class)->get();
        $this->processUrls($UrlsToScrape);

        self::$_IS_SCRAPING = false;
    }

    /**
     * Scrape a specific URL, storing page content in
     * the index table
     * 
     * @param string $url   The URL to scrape
     * @return void
     */
    public function scrapeUrl(string $url): void
    {
        self::$_IS_SCRAPING = true;

        $UrlsToScrape = Url::withoutGlobalScope(LanguageScope::class)->where('url', 'LIKE', $url)->get();
        $this->processUrls($UrlsToScrape);

        self::$_IS_SCRAPING = false;
    }

    /**
     * Process the provided collection of URLs, scraping their content
     * for the search index
     * 
     * @param Collection $UrlsToScrape
     * @return void
     */
    protected function processUrls(Collection $UrlsToScrape): void
    {
        foreach ($UrlsToScrape as $Url) {
            if (!$Url->urlable) {
                $this->outputCommandLine($Url->url . ' skipped - linked resources not available.');
                continue;
            }
            
            if (!$this->urlNeedsIndexing($Url)) {
                $this->outputCommandLine($Url->url . ' skipped - not updated since last index.');
                continue;
            }

            $page_content = $this->getUrlContent($Url);
            if (empty($page_content)) {
                $this->outputCommandLine($Url->url . ' returned no content.');
                continue;
            }

            $Index = $this->getIndexForUrl($Url);
            if ($Index) {
                $Index->url = $Url->url;
                $Index->content = $page_content;
                $Index->save();
            } else {
                SearchIndex::create([
                    'url_id'        => $Url->id,
                    'language_id'   => $Url->language_id,
                    'url'           => $Url->url,
                    'content'       => $page_content
                ]);
            }

            $this->outputCommandLine($Url->url . ' scaped.');
        }
    }

    /**
     * Check if the provided URL needs indexing, based on it's
     * updated_at timestamp
     * 
     * @param Url $Url
     * @return bool
     */
    protected function urlNeedsIndexing(Url $Url): bool
    {
        if ($this->recrawl_forced) {
            return true;
        }
        
        $Index = $this->getIndexForUrl($Url);
        if (!$Index) { return true; }
        if (strtotime($Index->updated_at) < strtotime($Url->updated_at)) { return true; }

        try {
            $LinkedResource = $Url->urlable;
            if (strtotime($Index->updated_at) < strtotime($LinkedResource->updated_at)) { return true; }
        } catch (Exception $e) {}

        return false;
    }

    /**
     * Get the search index record for the provided URL,
     * or null if not found
     * 
     * @param Url $Url
     * @return ?SearchIndex
     */
    protected function getIndexForUrl(Url $Url): ?SearchIndex
    {
        return SearchIndex::withoutGlobalScope(LanguageScope::class)
            ->where('url_id', $Url->id)->first();
    }

    /**
     * Get the page content for the provided URL
     * 
     * @param Url $Url
     * @return string
     */
    protected function getUrlContent(Url $Url): string
    {
        $SiteLang = LanguageHelper::getLanguage();

        $content = '';
        try {
            LanguageHelper::overrideLanguage($Url->language_id);
            $response = $this->RouteController->route($Url->url);
            if ($response instanceof View) {
                $content = $response->toHtml();
                $content = $this->getHtmlBody($content);
            }
        } catch (Exception $e) {
            $this->logMessage('Error loading content for ' . $Url->url);
            $this->logMessage($e);
        }

        LanguageHelper::overrideLanguage($SiteLang->id);

        return $content;
    }

    /**
     * Returns the content of <body> from the provided $html
     * document, with disallowed tags removed
     * 
     * @param string $html
     * @return string
     */
    protected function getHtmlBody(string $html): string
    {
        libxml_use_internal_errors(true);
        $Doc = new DOMDocument();
        $BodyDoc = new DOMDocument();

        $Doc->loadHTML($html);
        $body = $Doc->getElementsByTagName('body')->item(0);
        foreach ($body->childNodes as $child) {
            if (in_array(strtolower($child->nodeName), ['header', 'footer', 'script', 'nav'])) { continue; }

            $BodyDoc->appendChild($BodyDoc->importnode($child, true));
        }

        $body_html = $BodyDoc->saveHTML();
        $line_removed_html = str_replace(["\r", "\n", "\r\n", '&#13;'], '', $body_html);
        $no_tags = strip_tags($line_removed_html);
        return trim(preg_replace('/(\s\s+)/', ' ', $no_tags));
    }

    /**
     * Check whether a scrape is in progress, and return
     * the value of the static variable
     * 
     * @return bool
     */
    public static function checkIsScraping(): bool
    {
        return self::$_IS_SCRAPING;
    }

    /**
     * Output the provided message to the command line
     * if set
     * 
     * @param string $message
     * @param string $type
     * @return void
     */
    protected function outputCommandLine(string $message, string $type = 'info'): void
    {
        if ($this->ConsoleCommand) {
            switch ($type) {
                case 'error': $this->ConsoleCommand->error($message); break;
                default: $this->ConsoleCommand->info($message); break;
            }
        }
    }

    /**
     * Logs the provided message to the default
     * channel
     * 
     * @param mixed $message
     * @return void
     */
    protected function logMessage(mixed $message): void
    {
        Log::debug($message);
    }
}