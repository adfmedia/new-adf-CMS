<?php

namespace App\Library;

use App\Helpers\RouteHelper;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;

class CachingQueryBuilder extends Builder
{
    /**
     * Execute the query as a "select" statement, caching
     * the query results.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = ['*']): \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array
    {
        if (!config('database.cache') || RouteHelper::isCmsRoute() || RouteHelper::isPreviewRoute()) {
            return parent::get($columns);
        }

        $sql_str = $this->toRawSql();
        return Cache::remember(
            'querycache:' . hash('sha256', $sql_str),
            config('database.cache_seconds'),
            function() use ($columns) {
                return parent::get($columns);
            }
        );
    }
}