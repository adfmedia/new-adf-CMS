<?php

namespace App\Casts;

use App\Helpers\ContentReplaceHelper;
use Illuminate\Database\Eloquent\Model;

class Wysiwyg extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return string
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        $content = (string)$value;
        return ContentReplaceHelper::replaceContentFromStorage($content);
    }

    /**
     * {@inheritdoc}
     * 
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $content = (string)$value;
        return ContentReplaceHelper::replaceContentForStorage($content);
    }
}