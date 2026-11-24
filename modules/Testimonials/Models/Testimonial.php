<?php

namespace Modules\Testimonials\Models;

use App\Casts\Image;
use App\Casts\Wysiwyg;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class Testimonial extends Model
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey;

    protected $table        = 'testimonials';
    protected $primaryKey   = ['testimonial_id', 'language_id', 'version'];
    public $incrementing    = false;

    protected $fillable     = [
        'testimonial_id',
        'language_id',
        'version',
        'title',
        'quote',
        'citation',
        'job_title',
        'image',
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
            'image' => Image::class,
            'quote' => Wysiwyg::class,
        ];
    }
}