<?php

namespace App\Models\Scopes;

use App\Helpers\LanguageHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LanguageScope implements Scope
{
    /**
     * {@inheritdoc}
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getTable() . '.language_id', LanguageHelper::getLanguage()->id);
    }
}
