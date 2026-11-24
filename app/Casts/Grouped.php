<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

class Grouped extends SharedAppAdmCast
{
    /**
     * Additional field type casting to perform within this cast
     * 
     * @var array
     */
    protected $field_casts  = [
        'file'          => File::class,
        'image'         => Image::class,
        'link'          => Link::class,
        'theme_map'     => ThemeMapped::class,
        'wysiwyg'       => Wysiwyg::class,
    ];

    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $stored = Str::isJson($value) ? (array)json_decode($value, true) : $value;

        if (is_array($stored)) {
            $grouped_fields = isset($attributes['block_grouped_fields']) ? $attributes['block_grouped_fields'] : $this->getGroupedFields($model, $key);
            if (!empty($grouped_fields)) {
                $stored = $this->processCastables(
                    $model,
                    $key,
                    $grouped_fields,
                    $stored,
                    'get'
                );
            }
        }

        return $stored;
    }

    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (Str::isJson($value)) { return $value; }
        
        $grouped_fields = isset($attributes['block_grouped_fields']) ? $attributes['block_grouped_fields'] : $this->getGroupedFields($model, $key);
        if (!empty($grouped_fields)) {
            $value = $this->processCastables(
                $model,
                $key,
                $grouped_fields,
                $value,
                'set'
            );
        }
        return json_encode($value);
    }

    /**
     * Process any castable fields on the provided row
     * 
     * @param Model $model
     * @param string $key
     * @param array $grouped_fields
     * @param array $grouped_row
     * @param string $cast_tyle
     * @return array
     */
    protected function processCastables(
        Model $model,
        string $key,
        array $grouped_fields,
        array $grouped_row,
        string $cast_type
    ): array
    {
        foreach ($grouped_row as $field => $value) {
            if (!isset($grouped_fields[$field])) {
                continue;
            }

            $field_type = $grouped_fields[$field]['type'];
            $castable_class = isset($this->field_casts[$field_type]) ? $this->field_casts[$field_type] : null;
            $castable_value = $value;
            $castable_attrs = [];

            switch ($field_type) {
                case 'theme_map':
                    $castable_attrs = [
                        'block_map_type' => $grouped_fields[$field]['map_type']
                    ];
                    break;
            }

            if (!is_null($castable_class) && class_exists($castable_class)) {
                $Castable = new $castable_class();
                if ($cast_type == 'set') {
                    $grouped_row[$field] = $Castable->set(
                        $model,
                        $key . '.' . $field,
                        $castable_value,
                        $castable_attrs
                    );
                } else {
                    $grouped_row[$field] = $Castable->get(
                        $model,
                        $key . '.' . $field,
                        $castable_value,
                        $castable_attrs
                    );
                }
            }
        }

        return $grouped_row;
    }

    /**
     * Return the provided model's grouped fields
     * 
     * @param Model $model
     * @param string $key
     * @return array
     */
    protected function getGroupedFields(Model $model, string $key): array
    {
        if (isset($model->config_class)) {
            $Conf = new ReflectionClass($model->config_class);
            $fields = $Conf->getStaticPropertyValue('fields');
            if (isset($fields[$key]) && isset($fields[$key]['fields'])) {
                return $fields[$key]['fields'];
            }
        }

        return [];
    }
}