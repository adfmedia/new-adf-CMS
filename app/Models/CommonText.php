<?php

namespace App\Models;

use App\Models\Scopes\LanguageScope;
use App\Models\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([LanguageScope::class])]
class CommonText extends Model
{
    use CachesQueries;
    
    protected $table        = 'common_text';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'field',
        'language_id',
        'value'
    ];
}