<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table        = 'languages';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'title',
        'locale',
        'base_url'
    ];

    /**
     * Order languages by the length of their base URLs,
     * so that multi-lingual ones appear prior to the
     * primary domain
     * 
     * @param \Illuminate\Database\Eloquent\Builder $Query
     * @return void
     */
    #[Scope]
    protected function urlMatchOrder(Builder $Query): void
    {
        $Query->orderByRaw('LENGTH(' . $this->table . '.base_url) DESC');
    }

    /**
     * Returns the 'slug' for this language's base URL,
     * e.g. "fr" for domain.com/fr
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function urlSlug(): Attribute
    {
        return Attribute::make(
            get: fn() => ltrim(parse_url($this->base_url, PHP_URL_PATH), '/')
        )->shouldCache();
    }

    /**
     * Returns the FQDN for this language, removing any subdirectories
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function fqdn(): Attribute
    {
        return Attribute::make(
            get: fn() => parse_url($this->base_url, PHP_URL_SCHEME) . '://' . parse_url($this->base_url, PHP_URL_HOST)
        )->shouldCache();
    }
}