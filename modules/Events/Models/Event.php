<?php

namespace Modules\Events\Models;

use App\Casts\Image;
use App\Casts\Wysiwyg;
use App\Models\CommonModel;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasUrl;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Events\App\Controllers\EventController;
use Modules\Events\Config\EventsConfig;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class Event extends CommonModel
{
    use SoftDeletes, HasCompositePrimaryKey, CachesQueries, HasUrl;

    protected $table        = 'events';
    protected $primaryKey   = ['event_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'event_id',
        'language_id',
        'version',
        'title',
        'event_date',
        'start_time',
        'end_time',
        'address',
        'image',
        'excerpt',
        'content',
        'status',
    ];

    public $config_class    = EventsConfig::class;

    public $route_binding   = [
        'controller'    => EventController::class,
        'method'        => 'event'
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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            EventCategory::class,
            'event_to_event_category',
            'event_id',
            'event_category_id',
            'event_id',
            'event_category_id'
        );
    }

    #[Scope]
    protected function inCategory(Builder $query, int $category_id)
    {
        $query->whereHas('categories', function (Builder $subquery) use ($category_id) {
            $subquery->where('event_categories.event_category_id', $category_id);
        });
    }

    #[Scope]
    protected function matchingMonthYear(Builder $query, string $archive_month): void
    {
        try {
            $Month = Carbon::createFromFormat("Y-m", $archive_month);
            $query->whereMonth($this->getTable() . '.event_date', $Month->format("m"))
                ->whereYear($this->getTable() . '.event_date', $Month->format("Y"));
        } catch (Exception $e) {}
    }

    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->whereDate($this->getTable() . '.event_date', '>=', date("Y-m-d"));
    }

    #[Scope]
    protected function dateOrder(Builder $query, bool $asc_order = true): void
    {
        $query->orderBy($this->getTable() . '.event_date', ($asc_order ? 'asc' : 'desc'))
            ->orderBy($this->getTable() . '.start_time', 'asc');
    }

    protected function carbonDate(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Carbon::parse($attributes['event_date'])
        )->shouldCache();
    }

    protected function carbonStartTime(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Carbon::createFromFormat("H:i:s", $attributes['start_time'])
        )->shouldCache();
    }

    protected function carbonEndTime(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Carbon::createFromFormat("H:i:s", $attributes['end_time'])
        )->shouldCache();
    }

    public static function getUpcomingMonths(): Collection
    {
        return self::query()
            ->upcoming()
            ->selectRaw('MONTH(events.event_date) AS month')
            ->selectRaw('YEAR(events.event_date) AS year')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->distinct()
            ->get();
    }
}