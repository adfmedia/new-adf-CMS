<?php

namespace App\Models;

use App\Casts\PageBuilderContentValue;
use App\Models\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Model;

class PageBuilderBlockContent extends Model
{
    use CachesQueries;
    
    public $table       = 'page_builder_block_contents';
    public $primaryKey  = 'id';

    protected $fillable = [
        'page_builder_block_id',
        'field',
        'value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => PageBuilderContentValue::class
        ];
    }
}
