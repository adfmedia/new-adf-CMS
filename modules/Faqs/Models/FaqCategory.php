<?php

namespace Modules\Faqs\Models;

use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use App\Models\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class FaqCategory extends Model
{
    use SoftDeletes, CachesQueries, HasCompositePrimaryKey;

    protected $table        = 'faq_categories';
    protected $primaryKey   = ['faq_category_id', 'language_id'];
    public $incrementing    = false;

    protected $fillable     = [
        'faq_category_id',
        'language_id',
        'title',
        'status'
    ];

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(
            Faq::class,
            'faq_to_faq_category',
            'faq_category_id',
            'faq_id',
            'faq_category_id',
            'faq_id'
        )->orderBy('faqs.priority', 'desc');
    }
}
