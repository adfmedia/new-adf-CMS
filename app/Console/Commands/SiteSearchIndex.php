<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Search\Library\SiteScrape;

class SiteSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature    = 'site_search:index
                               {--url= : A specific URL slug to index}
                               {--force : Include to force a re-crawl of all known URLs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description  = 'Process website URL(s) to generate the search index when the search module is enabled';

    /**
     * Execute the command
     * 
     * @return void
     */
    public function handle(): void
    {
        if (in_array('Search', config('modules.available'))) {
            $Scraper = new SiteScrape($this);

            if ($this->option('force')) {
                $Scraper->forceRecrawl();
            }

            $url = $this->option('url');
            if ($url) {
                $Scraper->scrapeUrl((string)$url);
            } else {
                $Scraper->scrapeSite();
            }
        }
    }
}