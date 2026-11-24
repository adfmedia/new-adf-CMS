<?php

namespace Modules\News\Models;

use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\News\App\Controllers\NewsController;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class NewsCategory extends CommonModel
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey, HasUrl;
    
    protected $table        = 'news_categories';
    protected $primaryKey   = ['news_category_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'news_category_id',
        'language_id',
        'version',
        'title',
        'h1_title',
        'status'
    ];

    public $route_binding = [
        'controller'    => NewsController::class,
        'method'        => 'category'
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(NewsArticle::class, 'news_category_id', 'news_category_id');
    }
}