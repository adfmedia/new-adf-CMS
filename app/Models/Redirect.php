<?php

namespace App\Models;

use App\Models\Scopes\StatusEnabledScope;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([StatusEnabledScope::class])]
class Redirect extends Model
{
    protected $table        = 'redirects';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'old_url',
        'new_url',
        'redirect_type',
        'no_match_only',
        'status'
    ];

    /**
     * Query scope used to search redirects, matching the
     * provided $url_from exactly, or with a trailing slash
     * 
     * @param Builder $query
     * @param string $url_from
     * @return void
     */
    #[Scope]
    protected function search(Builder $query, string $url_from): void
    {
        $query->where(function (Builder $subquery) use ($url_from) {
            $subquery->where('old_url', $url_from)
                ->orWhere('old_url', $url_from . '/');
        });
    }

    /**
     * Query scope used to perform wildcard searches on redirects
     * 
     * @param Builder $query
     * @param string $url_from
     * @return void
     */
    #[Scope]
    protected function wildcardSearch(Builder $query, string $url_from): void
    {
        $query->whereRaw('? LIKE REPLACE(old_url, "*", "%")', [$url_from]);
    }

    /**
     * Query scope used to restrict redirect lookups to those
     * not flagged as only if page not found
     * 
     * @param Builder $Query
     * @return void
     */
    #[Scope]
    protected function beforeUrlLookup(Builder $Query): void
    {
        $Query->where('no_match_only', false);
    }

    /**
     * Query scope used to restrict redirect lookups to those
     * flagged as only if page not found
     * 
     * @param Builder $Query
     * @return void
     */
    #[Scope]
    protected function afterUrlLookup(Builder $Query): void
    {
        $Query->where('no_match_only', true);
    }

    /**
     * Morphs the stored 'new' URL into an absolute URL if
     * stored as relative
     * 
     * @return Attribute
     */
    protected function newAbsoluteUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $value = $attributes['new_url'];
                if (stripos($value, 'http:') === 0 || stripos($value, 'https:') === 0) {
                    return $value;
                }

                return route('theme', $value);
            }
        )->shouldCache();
    }

    /**
     * Get the redirects 'type' (i.e. HTTP code), either as 302 or 301
     * 
     * @return Attribute
     */
    protected function redirectType(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $value == 302 ? 302 : 301
        )->shouldCache();
    }

    /**
     * Parses a redirect 'to' URL, replacing any wildcard elements
     * with the relevant sections of the current URL
     * 
     * @param string $url_path
     * @param Redirect $Redirect
     * @return string
     */
    public static function parseRedirectTo(string $url_path, Redirect $Redirect): string
    {
        $redirect_to = $Redirect->new_absolute_url;
        if (stristr($redirect_to, '*') !== false) {
            $old_parts = array_filter(explode('*', $Redirect->old_url));
            $old_wildcard = str_replace($old_parts, '', $url_path);
            $redirect_to = str_replace('*', $old_wildcard, $redirect_to);
        }

        return $redirect_to;
    }
}