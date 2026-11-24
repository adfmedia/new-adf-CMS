<?php

namespace Modules\Faqs\Models;

use App\Casts\Wysiwyg;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\Traits\HasPriority;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class Faq extends Model
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey, HasPriority;

    protected $table        = 'faqs';
    protected $primaryKey   = ['faq_id', 'language_id'];
    public $incrementing    = false;

    protected $fillable     = [
        'faq_id',
        'language_id',
        'title',
        'question',
        'answer',
        'priority',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'answer'    => Wysiwyg::class,
        ];
    }
}