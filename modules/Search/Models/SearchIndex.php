<?php

namespace Modules\Search\Models;

use App\Models\Scopes\LanguageScope;
use App\Models\Url;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([LanguageScope::class])]
class SearchIndex extends Model
{
    protected $table        = 'search_index';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'url_id',
        'language_id',
        'url',
        'content'
    ];

    /**
     * Relationship to the search index's URL
     * record
     * 
     * @return BelongsTo
     */
    public function url_record(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'url_id', 'id');
    }

    /**
     * Searches scraped content for the provided term
     * using a FULL TEXT match
     *
     * @param Builder $query
     * @param string $term
     * @return void
     */
    #[Scope]
    protected function searchContent(Builder $query, string $term): void
    {
        $query->whereRaw("MATCH (content) AGAINST (?)", $this->removeReservedSymbols($term));
    }

    /**
     * Restrict searches to URLs of specific model classes
     * 
     * @param Builder $Query
     * @param array $classes
     * @return void
     */
    #[Scope]
    protected function matchingClasses(Builder $Query, array $classes = []): void
    {
        $Query->whereHas('url_record', function (Builder $Subquery) use ($classes) {
            $Subquery->whereIn('urls.urlable_type', $classes);
        });
    }

    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function removeReservedSymbols(string $term): string
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        return str_replace($reservedSymbols, '', $term);
    }
}