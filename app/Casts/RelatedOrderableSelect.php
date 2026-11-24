<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

class RelatedOrderableSelect extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($this->shouldLoadCMSDetails()) {
            return $this->returnValueForCMS($value);
        }

        $model_fn = Str::camel('get_related_' . $key);
        if (method_exists($model, $model_fn)) {
            return $model->{$model_fn}($value);
        }

        return $this->returnRelatedCollection($model, $key, $value, $attributes);
    }

    /**
     * {@inheritdoc}
     * 
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value);
    }

    /**
     * Return a version of the stored data, prepared for the CMS
     * 
     * @param mixed $value
     * @return array
     */
    protected function returnValueForCMS(mixed $value): array
    {
        if (empty($value) && (!Str::isJson($value) || !is_array($value))) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        return (array)json_decode($value, true);
    }

    /**
     * Return a version of the stored data, as a collection ready for the front-end
     * 
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return Collection
     */
    protected function returnRelatedCollection(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        $Related = new Collection();
        if (empty($value) && (!Str::isJson($value) || !is_array($value))) {
            return $Related;
        }

        if (Str::isJson($value)) {
            $value = json_decode($value, true);
        }

        if (!isset($model->config_class) && !isset($attributes['block_related_class'])) {
            return $Related;
        }

        if (isset($attributes['block_related_class'])) {
            $related_class = $attributes['block_related_class'];
        } else {
            $Conf = new ReflectionClass($model->config_class);
            $fields = $Conf->getStaticPropertyValue('fields');
            if (!isset($fields[$key]) || !isset($fields[$key]['relation']) || !isset($fields[$key]['relation']['to'])) {
                return $Related;
            }

            $related_class = $fields[$key]['relation']['to'];
        }
        
        $RelatedInst = new $related_class();
        $related_class_id = $this->getRelatedClassIdField($RelatedInst);
        if(is_array($value)) {
            foreach ($value as $related_id) {
                $ThisRelated = $RelatedInst->query()->where($related_class_id, $related_id)->first();
                if ($ThisRelated) {
                    $Related->push($ThisRelated);
                }
            }
        }
    
        return $Related;
    }

    /**
     * Return the 'primary', related class ID field for the provided model
     * 
     * @param Model $model
     * @return string
     */
    protected function getRelatedClassIdField(Model $model): string
    {
        if (method_exists($model, 'getMorphableIdField')) {
            return $model->getMorphableIdField();
        }

        $keys = $model->getKeyName();
        if (!is_array($keys)) {
            return $keys;
        }

        foreach ($keys as $key) {
            if (!in_array($key, ['language_id', 'version'])) {
                return $key;
            }
        }

        return reset($keys);
    }
}