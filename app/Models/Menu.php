<?php

namespace App\Models;

use App\Casts\MenuStructure;
use App\Models\Scopes\LanguageScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([LanguageScope::class])]
class Menu extends Model
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey;

    protected $table        = 'menus';
    protected $primaryKey   = ['menu_id', 'language_id'];
    public $incrementing    = false;

    protected $fillable     = [
        'menu_id',
        'language_id',
        'title',
        'code_reference',
        'structure',
        'nest_limit'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'structure' => MenuStructure::class
        ];
    }

    /**
     * Returns the menu matching the provided reference, or
     * null if not found
     * 
     * @param string $code_reference
     * @return ?Menu
     */
    public static function getMenu(string $code_reference): ?Menu
    {
        return self::where('code_reference', $code_reference)->first();
    }
}