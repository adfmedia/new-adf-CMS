<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class ThemeMapped extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($this->shouldLoadCMSDetails()) {
            return $value;
        }

        $map_type = isset($attributes['block_map_type']) ? $attributes['block_map_type'] : $this->getMappingType($model, $key);
        return config('theme_map.' . $map_type . '.' . $value . '.class', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Get the repeatable field configuration for the current field
     * 
     * @param Model $model
     * @param string $key
     * @return ?string
     */
    protected function getMappingType(Model $model, string $key): ?string
    {
        if (isset($model->config_class)) {
            $Conf = new ReflectionClass($model->config_class);
            $fields = $Conf->getStaticPropertyValue('fields');
            if (isset($fields[$key]) && isset($fields[$key]['map_type'])) {
                return $fields[$key]['map_type'];
            }
        }

        return null;
    }
}