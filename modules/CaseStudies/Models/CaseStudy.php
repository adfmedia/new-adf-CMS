<?php

namespace Modules\CaseStudies\Models;

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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CaseStudies\App\Controllers\CaseStudyController;
use Modules\CaseStudies\Config\CaseStudiesConfig;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class CaseStudy extends CommonModel
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey, HasUrl;

    protected $table        = 'case_studies';
    protected $primaryKey   = ['case_study_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'case_study_id',
        'language_id', 
        'version',
        'title',
        'h1_title',
        'image',
        'excerpt',
        'content',
        'date',
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
            'image'     => Image::class,
            'content'   => Wysiwyg::class,
        ];
    }

    public $config_class    = CaseStudiesConfig::class;

    public $route_binding   = [
        'controller'    => CaseStudyController::class,
        'method'        => 'article'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CaseStudyCategory::class,
            'case_study_to_case_study_category',
            'case_study_id',
            'case_study_category_id',
            'case_study_id',
            'case_study_category_id'
        );
    }

    #[Scope]
    protected function inCategory(Builder $query, int $category_id)
    {
        $query->whereHas('categories', function (Builder $subquery) use ($category_id) {
            $subquery->where('case_study_categories.case_study_category_id', $category_id);
        });
    }

    protected function dateCarbon(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Carbon::parse($attributes['date'])
        )->shouldCache();
    }
}