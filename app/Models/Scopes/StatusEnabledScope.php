<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StatusEnabledScope implements Scope
{
    /**
     * {@inheritdoc}
     */
    public function apply(Builder $builder, Model $model) : void
    {
        $builder->where($model->getTable() . '.status', 1);
    }
}