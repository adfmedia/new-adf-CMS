<?php

namespace Modules\TeamMembers\Models;

use App\Casts\Image;
use App\Casts\PageBuilder;
use App\Casts\Repeatable;
use App\Casts\Wysiwyg;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasPriority;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\TeamMembers\App\Controllers\TeamMemberController;
use Modules\TeamMembers\Config\TeamMembersConfig;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class TeamMember extends Model
{
    use SoftDeletes, HasCompositePrimaryKey, CachesQueries,
        HasUrl, HasPriority;

    protected $table        = 'team_members';
    protected $primaryKey   = ['team_member_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'team_member_id',
        'language_id',
        'version',
        'first_name',
        'last_name',
        'image',
        'job_title',
        'email',
        'telephone',
        'social_links',
        'short_description',
        'content',
        'priority',
        'status'
    ];

    public $config_class    = TeamMembersConfig::class;
    public $route_binding   = [
        'controller'    => TeamMemberController::class,
        'method'        => 'team_member'
    ];
    public $url_search_field    = 'first_name';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image'         => Image::class,
            'content'       => Wysiwyg::class,
            'social_links'  => Repeatable::class,
            'page_builder'  => PageBuilder::class
        ];
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name']
        )->shouldCache();
    }
}