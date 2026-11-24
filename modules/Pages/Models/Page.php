<?php

namespace Modules\Pages\Models;

use App\Casts\PageBuilder;
use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class Page extends CommonModel
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey, HasUrl;
    
    protected $table        = 'pages';
    protected $primaryKey   = ['page_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'page_id',
        'language_id',
        'version',
        'parent_id',
        'title',
        'h1_title',
        'status'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_builder'  => PageBuilder::class
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'page_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'page_id');
    }
}