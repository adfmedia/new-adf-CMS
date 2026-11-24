<?php

namespace App\Models;

use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

#[ScopedBy([LanguageScope::class])]
class Url extends Model
{
    use SoftDeletes, CachesQueries;

    protected $table        = 'urls';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'url',
        'urlable_id',
        'urlable_type',
        'language_id',
        'meta_title',
        'meta_description',
        'no_index',
        'system_page',
        'route'
    ];

    /**
     * Relationship to the URL's language
     * 
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Find the model corresponding to this URL
     * 
     * @return MorphTo
     */
    public function urlable(): MorphTo
    {
        return $this->morphTo(null, null, null, $this->getRelatedRecordOwnerKey());
    }

    /**
     * Get the 'primary' key field for the related model
     * 
     * @return mixed
     */
    protected function getRelatedRecordOwnerKey(): mixed
    {
        if (in_array(HasUrl::class, class_uses_recursive($this->urlable_type))) {
            $BlankModel = new $this->urlable_type();
            return $BlankModel->getOwnerKeyName();
        }
        return null;
    }

    /**
     * Get the morphed model for the system page matching the controller
     * & method provided
     * 
     * @param string $controller
     * @param string $method
     * @return ?Model
     */
    public static function getSystemPage(string $controller, string $method): ?Model
    {
        $Url = self::getUrlRecordForSystemPage($controller, $method);
        return $Url ? $Url->urlable : null;
    }

    /**
     * Get the morphed model for the system page matching the controller
     * & method provided, even if it's disabled
     * 
     * @param string $controller
     * @param string $method
     * @return ?Model
     */
    public static function getDisabledSystemPage(string $controller, string $method): ?Model
    {
        $Url = self::getUrlRecordForSystemPage($controller, $method);
        return $Url ? $Url->urlable()->withoutGlobalScope(StatusEnabledScope::class)->first() : null;
    }

    /**
     * Get the URL record for the system page matching the controller
     * & method provided
     * 
     * @param string $controller
     * @param string $method
     * @return ?self
     */
    protected static function getUrlRecordForSystemPage(string $controller, string $method): ?self
    {
        if (!Schema::hasTable('urls')) {
            return null;
        }

        return self::where([
            'system_page'   => 1,
            'route'         => 'controller=' . $controller . '&method=' . $method
        ])->first();
    }

    /**
     * Returns the main 'account' page for logged in users
     * 
     * @return ?Model
     */
    public static function getAccountPage(): ?Model
    {
        return self::getSystemPage('account', 'profile');
    }

    /**
     * Query scope to retrieve indexable URLs
     * 
     * @param Builder $query
     * @return void
     */
    #[Scope]
    protected function indexable(Builder $query): void
    {
        $query->where($this->getTable() . '.no_index', '<>', 1);
    }

    /**
     * Returns all related URLs for the same page in
     * different languages
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function translationAlternateHrefs(): Attribute
    {
        return Attribute::make(
            get: fn() => self::query()
                ->withoutGlobalScope(LanguageScope::class)
                ->where([
                    'urlable_id'    => $this->urlable_id,
                    'urlable_type'  => $this->urlable_type
                ])
                ->where('language_id', '<>', $this->language_id)
                ->get()
                ->pluck('alternate_href', 'language.locale')
                ->toArray()
        )->shouldCache();
    }

    /**
     * Return the full translated version of this URL
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function alternateHref(): Attribute
    {
        return Attribute::make(
            get: function () {
                $base_url = data_get($this, 'language.base_url');
                $locale_slug = data_get($this, 'language.url_slug');
                if (strlen($locale_slug) > 0) {
                    $base_url = substr($base_url, 0, strlen($locale_slug) * -1);
                }
                return rtrim($base_url, '/') . '/' . trim($this->url, '/');
            }
        )->shouldCache();
    }
}