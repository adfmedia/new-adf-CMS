<?php

namespace Modules\Tests\Models;

use App\Casts\File;
use App\Casts\Image;
use App\Casts\Link;
use App\Casts\Repeatable;
use App\Models\CommonModel;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasUrl;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Tests\Config\TestsConfig;

class Test extends CommonModel
{
    use SoftDeletes, HasCompositePrimaryKey, HasUrl;

    protected $table        = 'tests';
    protected $primaryKey   = ['test_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'test_id',
        'language_id',
        'version',
        'boolean',
        'checkbox',
        'colour',
        'date',
        'date_time',
        'email',
        'encrypted',
        'file',
        'image',
        'link',
        'number',
        'orderable_select',
        'password',
        'page_id',
        'radio',
        'repeatable',
        'select',
        'stacked_repeatable',
        'string',
        'telephone',
        'textarea',
        'time',
        'wysiwyg'
    ];

    public $config_class    = TestsConfig::class;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checkbox'              => 'array',
            'encrypted'             => 'encrypted',
            'file'                  => File::class,
            'image'                 => Image::class,
            'link'                  => Link::class,
            'orderable_select'      => 'array',
            'repeatable'            => Repeatable::class,
            'stacked_repeatable'    => Repeatable::class
        ];
    }
}
