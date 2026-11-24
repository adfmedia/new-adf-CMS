<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CommonModel extends Model
{
    /**
     * Returns the H1 title for the current model, or
     * it's title if empty
     * 
     * @return Attribute
     */
    protected function h1Title(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $value ?? (isset($attributes['title']) ? $attributes['title'] : '')
        );
    }
}