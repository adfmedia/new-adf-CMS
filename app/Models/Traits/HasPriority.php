<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasPriority
{
    /**
     * Put the query results in 'priority' order, and optionally
     * by a secondary 'alpha' field (i.e. order by priority, then title)
     * 
     * @param Builder $query
     * @return void
     */
    #[Scope]
    protected function priorityOrder(Builder $query): void
    {
        $query->orderBy($this->getTable() . '.priority', 'desc');

        if (property_exists($this, 'priority_alpha_field')) {
            $query->orderBy($this->getTable() . '.' . $this->priority_alpha_field, 'asc');
        }
    }
}