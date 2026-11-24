<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageBuilderContentValue extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (Str::isJson($value)) {
            $value = json_decode($value, true);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }
}