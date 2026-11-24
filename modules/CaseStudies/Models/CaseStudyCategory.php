<?php

namespace Modules\CaseStudies\Models;

use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CaseStudies\App\Controllers\CaseStudyController;
use Modules\CaseStudies\Config\CaseStudyCategoriesConfig;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class CaseStudyCategory extends CommonModel
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey, HasUrl;

    protected $table        = 'case_study_categories';
    protected $primaryKey   = ['case_study_category_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'case_study_category_id',
        'language_id',
        'version',
        'title',
        'h1_title',
        'status'
    ];

    public $config_class    = CaseStudyCategoriesConfig::class;

    public $route_binding   = [
        'controller'    => CaseStudyController::class,
        'method'        => 'category'
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(
            CaseStudy::class, 
            'case_study_to_case_study_category', 
            'case_study_category_id', 
            'case_study_id',
            'case_study_category_id',
            'case_study_id'
        );
    }
}
