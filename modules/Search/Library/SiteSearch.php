<?php

namespace Modules\Search\Library;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Search\Models\SearchIndex;

class SiteSearch
{
    /**
     * The number of search results to display per page
     * 
     * @var int
     */
    const RESULTS_PER_PAGE  = 12;

    /**
     * Performs a search over all indexed content for the
     * provided search term, returning a paginated collection
     * of results
     * 
     * @param string $search_term   The term to search for
     * @param ?array $model_scoped  Optionally pass an array of model classes to restrict results
     * @return LengthAwarePaginator
     */
    public function search(string $search_term, array $model_scoped = []): LengthAwarePaginator
    {
        $Query = SearchIndex::query()
            ->searchContent($search_term);

        if (!empty($model_scoped)) {
            $Query->matchingClasses($model_scoped);
        }
        
        $IndexedUrls = $Query->get();

        if ($IndexedUrls->isEmpty()) {
            return $this->getPaginatedResults(new Collection(), 0);
        }

        $IndexedUrls = $IndexedUrls->filter(function (SearchIndex $IndexedUrl) {
            try {
                return $IndexedUrl && $IndexedUrl->url_record && $IndexedUrl->url_record->urlable;
            } catch (Exception $e){}
            return false;
        });
        if ($IndexedUrls->isEmpty()) {
            return $this->getPaginatedResults(new Collection(), 0);
        }

        $total_results = $IndexedUrls->count();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $offset = ($page - 1) * self::RESULTS_PER_PAGE;
        $IndexedResults = $IndexedUrls->skip($offset)->take(self::RESULTS_PER_PAGE);

        $Results = new Collection();
        foreach ($IndexedResults as $IndexedUrl) {
            $Result = $IndexedUrl->url_record->urlable;
            data_set($Result, 'search_snippet', $this->getHighlightedSnippet($IndexedUrl->content, $search_term));

            $Results->push($Result);
        }

        return $this->getPaginatedResults($Results, $total_results);
    }

    /**
     * Returns the indexed content matching the search term
     * with the search term wrapped in a <span> used for styling
     * 
     * @param string $content
     * @param string $search_term
     * @return string
     */
    protected function getHighlightedSnippet(string $content, string $search_term): string
    {
        $match_pos = stripos($content, $search_term);
        $substr_start = $match_pos > 50 ? ($match_pos - 50) : 0;
        $content = substr($content, $substr_start, 250);

        return preg_replace(
            '/(' . $search_term . ')/i',
            '<span class="search-highlight">$1</span>',
            $content
        );
    }

    /**
     * Returns the provided results in a paginated collection
     * 
     * @param Collection $Results
     * @param int $total
     * @return LengthAwarePaginator
     */
    protected function getPaginatedResults(Collection $Results, int $total): LengthAwarePaginator
    {
        $Paginated = new LengthAwarePaginator($Results, $total, self::RESULTS_PER_PAGE);
        $Paginated->appends(request()->except('page'));

        return $Paginated;
    }
}