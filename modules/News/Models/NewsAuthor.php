<?php

namespace Modules\News\Models;

use App\Casts\Image;
use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\News\Config\NewsAuthorsConfig;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class NewsAuthor extends CommonModel
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey;
    
    protected $table        = 'news_authors';
    protected $primaryKey   = ['news_author_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'news_author_id',
        'language_id',
        'version',
        'title',
        'image',
        'status'
    ];

    public $config_class = NewsAuthorsConfig::class;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image' => Image::class
        ];
    }
}