<?php

namespace Modules\Offices\Models;

use App\Casts\Image;
use App\Casts\PageBuilder;
use App\Casts\Repeatable;
use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasPriority;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Offices\App\Controllers\OfficeController;
use Modules\Offices\Config\OfficesConfig;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class Office extends CommonModel
{
    use SoftDeletes, HasCompositePrimaryKey, CachesQueries,
        HasUrl, HasPriority;

    protected $table        = 'offices';
    protected $primaryKey   = ['office_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'office_id',
        'language_id',
        'version',
        'title',
        'h1_title',
        'image',
        'email',
        'telephone',
        'address',
        'opening_times',
        'latitude',
        'longitude',
        'priority',
        'status'
    ];

    public $config_class    = OfficesConfig::class;
    public $route_binding   = [
        'controller'    => OfficeController::class,
        'method'        => 'office'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image'         => Image::class,
            'opening_times' => Repeatable::class,
            'page_builder'  => PageBuilder::class
        ];
    }
}