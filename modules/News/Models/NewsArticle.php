<?php

namespace Modules\News\Models;

use App\Casts\Image;
use App\Casts\Wysiwyg;
use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasUrl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\News\App\Controllers\NewsController;
use Modules\News\Config\NewsArticlesConfig;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class NewsArticle extends CommonModel
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey, HasUrl;

    protected $table        = 'news_articles';
    protected $primaryKey   = ['news_article_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'news_article_id',
        'language_id',
        'version',
        'news_category_id',
        'news_author_id',
        'title',
        'h1_title',
        'excerpt',
        'content',
        'image',
        'article_date',
        'status'
    ];

    public $config_class    = NewsArticlesConfig::class;

    public $route_binding = [
        'controller'    => NewsController::class,
        'method'        => 'article'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image'     => Image::class,
            'content'   => Wysiwyg::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id', 'news_category_id');
    }

    #[Scope]
    protected function inCategory(Builder $query, int $category_id): void
    {
        $query->where($this->getTable() . '.news_category_id', $category_id);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(NewsAuthor::class, 'news_author_id', 'news_author_id');
    }

    protected function articleDateCarbon(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Carbon::parse($attributes['article_date'])
        )->shouldCache();
    }

    protected function schemaDate(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Carbon::parse($attributes['article_date'])
        )->shouldCache();
    }
}