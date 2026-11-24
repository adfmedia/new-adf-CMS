<?php

namespace App\Models\Traits;

use App\Models\Url;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasUrl
{
    /**
     * Return the URL record, morhped from the current model
     * 
     * @return MorphOne
     */
    public function url_record(): MorphOne
    {
        $local_key = $this->getKeyName();
        if (is_array($local_key)) {
            $pk = $local_key;
            foreach ($local_key as $key_part) {
                if ($key_part == 'language_id')  continue; 
                if ($key_part == 'version')  continue; 
                $pk = $key_part;
            }
            $local_key = $pk;
        }
        return $this->morphOne(Url::class, 'urlable', null, null, $local_key);
    }

    /**
     * Return the full URL path for the current model
     * 
     * @return Attribute
     */
    protected function url(): Attribute
    {
        $Model = $this;
        return Attribute::make(
            get: function (mixed $value) use ($Model) {
                return route('theme', ($Model->url_record ? $Model->url_record->url : ''));
            }
        )->shouldCache();
    }

    /**
     * Return the relative URL slug for the current model
     * 
     * @return Attribute
     */
    protected function urlSlug(): Attribute
    {
        $Model = $this;
        return Attribute::make(
            get: function (mixed $value) use ($Model) {
                return $Model->url_record ? $Model->url_record->url : '';
            }
        )->shouldCache();
    }

    /**
     * Get the boolean flag stating if the current model
     * is a system page or not
     * 
     * @return Attribute
     */
    protected function isSystemPage(): Attribute
    {
        $Model = $this;
        return Attribute::make(
            get: function (mixed $value) use ($Model) {
                return $Model->url_record && $Model->url_record->system_page;
            }
        )->shouldCache();
    }
}