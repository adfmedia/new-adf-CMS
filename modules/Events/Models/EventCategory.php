<?php

namespace Modules\Events\Models;

use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasPriority;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Events\App\Controllers\EventController;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class EventCategory extends CommonModel
{
    use SoftDeletes, HasCompositePrimaryKey, CachesQueries, 
        HasUrl, HasPriority;

    protected $table        = 'event_categories';
    protected $primaryKey   = ['event_category_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'event_category_id',
        'language_id',
        'version',
        'title',
        'priority',
        'status',
    ];

    public $route_binding   = [
        'controller'    => EventController::class,
        'method'        => 'category'
    ];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(
            Event::class,
            'event_to_event_category',
            'event_category_id',
            'event_id',
            'event_category_id',
            'event_id'
        );
    }
}
