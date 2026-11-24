<?php

namespace App\Casts;

use App\Helpers\RouteHelper;
use Closure;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Modules\Search\Library\SiteScrape;

abstract class SharedAppAdmCast implements CastsAttributes
{
    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Method used to determine if the CMS version of stored details should be
     * returned or not
     * 
     * @return bool
     */
    protected function shouldLoadCMSDetails(): bool
    {
        return RouteHelper::isCmsRoute() && !SiteScrape::checkIsScraping();
    }

    /**
     * Centralised function used to return a value from the get() method, and
     * store it in the cache
     * 
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @param Closure $callback
     * @return mixed
     */
    protected function getWithCache(Model $model, string $key, mixed $value, array $attributes, Closure $callback): mixed
    {
        if (RouteHelper::isPreviewRoute()) {
            return $callback();
        }
        
        $cache_key = $this->getFieldCacheKey($model, $key, $attributes);
        $cache_time = $this->shouldLoadCMSDetails() ? 60 : config('database.cache_seconds');
        return Cache::remember($cache_key, $cache_time, $callback);
    }

    /**
     * Get a consistent cache key for the current model's field $key
     * 
     * @param Model $model
     * @param string $key
     * @param array $attributes
     * @return string
     */
    protected function getFieldCacheKey(Model $model, string $key, array $attributes): string
    {
        $key_parts = [$model->getTable()];
        $local_key = $model->getKeyName();
        if (is_array($local_key)) {
            foreach ($local_key as $key_part) {
                $key_parts[] = data_get($model, $key_part);
            }
        } else {
            $key_parts[] = data_get($model, $local_key);
        }
        $key_parts[] = $key;

        if ($this->shouldLoadCMSDetails()) {
            $key_parts[] = 'cms';
        }
        if (RouteHelper::isPreviewRoute()) {
            $key_parts[] = 'preview';
        }

        return implode('.', $key_parts);
    }
}