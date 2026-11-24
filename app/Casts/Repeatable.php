<?php

namespace App\Casts;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

class Repeatable extends SharedAppAdmCast
{
    /**
     * Additional field type casting to perform within this cast
     * 
     * @var array
     */
    protected $field_casts  = [
        'file'          => File::class,
        'grouped'       => Grouped::class,
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
        return $this->getWithCache(
            $model, $key, $value, $attributes, 
            function () use ($model, $key, $value, $attributes) {
                $stored = Str::isJson($value) ? (array)json_decode($value, true) : $value;

                if (is_array($stored)) {
                    $repeatable_fields = isset($attributes['block_repeatable_fields']) ? $attributes['block_repeatable_fields'] : $this->getRepeatableFields($model, $key);
                    if (!empty($repeatable_fields)) {
                        $stored = $this->processCastables(
                            $model,
                            $key,
                            $repeatable_fields,
                            $stored,
                            'get'
                        );
                    }
                }

                $callback = isset($attributes['block_repeatable_callback']) ? $attributes['block_repeatable_callback'] : $this->getRepeatableCallback($model, $key);
                if (!empty($callback) && is_array($callback)) {
                    $callback_class = $callback[0];
                    $callback_function = $callback[1];
                    if (!class_exists($callback_class)) {
                        throw new Exception('Callback class ' . $callback_class . ' does not exist.');
                    }

                    $RefClass = new ReflectionClass($callback_class);
                    if (!$RefClass->hasMethod($callback_function)) {
                        throw new Exception('Callback function ' . $callback_function . ' does not exist on ' . $callback_class);
                    }

                    $stored = $RefClass->getMethod($callback_function)->invoke(null, $stored);
                }

                return $stored;
            }
        );
    }

    /**
     * {@inheritdoc}
     * 
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (Str::isJson($value)) { return $value; }
        
        $repeatable_fields = isset($attributes['block_repeatable_fields']) ? $attributes['block_repeatable_fields'] : $this->getRepeatableFields($model, $key);
        if (!empty($repeatable_fields)) {
            $value = $this->processCastables(
                $model,
                $key,
                $repeatable_fields,
                $value,
                'set'
            );
        }
        return json_encode($value);
    }

    /**
     * Process any castable data stored within the repeatable data
     * 
     * @param Model $model
     * @param string $key
     * @param array $repeatable_fields
     * @param array $repeatable_rows
     * @param string $cast_type
     * @return array
     */
    protected function processCastables(
        Model $model, 
        string $key, 
        array $repeatable_fields, 
        array $repeatable_rows, 
        string $cast_type
    ): array
    {
        foreach ($repeatable_rows as $row_idx => $row) {
            foreach ($row as $field => $value) {
                if (!isset($repeatable_fields[$field])) {
                    continue;
                }

                $field_type = $repeatable_fields[$field]['type'];
                $castable_class = isset($this->field_casts[$field_type]) ? $this->field_casts[$field_type] : null;
                $castable_value = $value;
                $castable_attrs = [];

                switch ($field_type) {
                    case 'image':
                        $castable_attrs = [
                            'block_config'  => [
                                'fields'        => $repeatable_fields,
                                'field_key'     => $field
                            ]
                        ];
                        break;
                    case 'grouped':
                        $castable_attrs = [
                            'block_grouped_fields'  => $repeatable_fields[$field]['fields']
                        ];
                        break;
                    case 'theme_map':
                        $castable_attrs = [
                            'block_map_type' => $repeatable_fields[$field]['map_type']
                        ];
                        break;
                }

                if (!is_null($castable_class) && class_exists($castable_class)) {
                    $Castable = new $castable_class();
                    if ($cast_type == 'set') {
                        $repeatable_rows[$row_idx][$field] = $Castable->set(
                            $model,
                            $key . '.' . $row_idx . '.' . $field,
                            $castable_value,
                            $castable_attrs
                        );
                    } else {
                        $repeatable_rows[$row_idx][$field] = $Castable->get(
                            $model,
                            $key . '.' . $row_idx . '.' . $field,
                            $castable_value,
                            $castable_attrs
                        );
                    }
                }
            }
        }

        return $repeatable_rows;
    }

    /**
     * Get the repeatable field configuration for the current field
     * 
     * @param Model $model
     * @param string $key
     * @return array
     */
    protected function getRepeatableFields(Model $model, string $key): array
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

    /**
     * Get an optionally assigned callback function for the current repeatable field
     * 
     * @param Model $model
     * @param string $key
     * @return mixed
     */
    protected function getRepeatableCallback(Model $model, string $key): mixed
    {
        if (isset($model->config_class)) {
            $Conf = new ReflectionClass($model->config_class);
            $fields = $Conf->getStaticPropertyValue('fields');
            if (isset($fields[$key]) && isset($fields[$key]['callback'])) {
                return $fields[$key]['callback'];
            }
        }

        return null;
    }
}