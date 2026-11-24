<?php

namespace App\Casts;

use App\Models\PageBuilderBlock;
use App\Models\Scopes\StatusEnabledScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class PageBuilder extends SharedAppAdmCast
{
    /**
     * Additional field type casting to perform within this cast
     * 
     * @var array
     */
    protected $field_casts  = [
        'file'                  => File::class,
        'grouped'               => Grouped::class,
        'image'                 => Image::class,
        'link'                  => Link::class,
        'repeatable'            => Repeatable::class,
        'stacked_repeatable'    => Repeatable::class,
        'theme_map'             => ThemeMapped::class,
        'wysiwyg'               => Wysiwyg::class,
    ];

    /**
     * {@inheritdoc}
     * 
     * @return Collection
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        $BlocksQry = PageBuilderBlock::with('contents')->where([
            'morphable_id'      => $model->{$this->getModelMorphableIdField($model)},
            'morphable_type'    => get_class($model),
            'morphable_field'   => $key,
            'language_id'       => $model->language_id,
            'version'           => $model->version,
        ])->orderBy('sort_order');
        if ($this->shouldLoadCMSDetails()) {
            $BlocksQry->withoutGlobalScope(StatusEnabledScope::class);
        }
        $Blocks = $BlocksQry->get();

        foreach ($Blocks as $Block) {
            $content = [];
            foreach ($Block->contents as $ContentRow) {
                $content[$ContentRow->field] = $ContentRow->value;
            }
            $content = $this->processBlockContent($model, $key, $Block, $content);
            data_set($Block, 'content', $content);
        }

        return $Blocks;
    }

    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) { return $value; }

        foreach ($value as $sort_order => $block_details) {
            if (empty($block_details['class'])) { continue; }

            if (empty($block_details['content'])) {
                $block_details['content'] = [];
            }

            $RefClass = new ReflectionClass($block_details['class']);
            $class_fields = $RefClass->getStaticPropertyValue('fields');
            $block_content = $block_details['content'];

            $block_content = $this->processCastables($model, $key, $sort_order, $class_fields, $block_content, 'set');

            data_set($block_details, 'content', $block_content);
            data_set($value, $sort_order, $block_details);
        }
        
        return $value;
    }

    /**
     * Process a block's content fields into usable information 
     * through additional casting & manipulation
     * 
     * @param Model $model
     * @param string $key
     * @param PageBuilderBlock $Block
     * @param array $content
     * @return array
     */
    protected function processBlockContent(Model $model, string $key, PageBuilderBlock $Block, array $content): array
    {
        $class_fields = $Block->getBlockReflectionClass()->getStaticPropertyValue('fields');
        $content = $this->processRelatedFields($class_fields, $content);
        $content = $this->processCastables($model, $key, $Block->sort_order, $class_fields, $content, 'get');

        if ($this->shouldLoadCMSDetails()) {
            return $content;
        }

        $RefClass = $Block->getBlockReflectionClass();
        if ($RefClass->hasMethod('processContents')) {
            $BlockClassInst = $RefClass->newInstance();
            $content = $RefClass->getMethod('processContents')->invoke($BlockClassInst, $content, $model, $Block);
        }

        return $content;
    }

    /**
     * Process any related fields within the provided content
     * 
     * @param array $class_fields
     * @param array $content
     * @return array
     */
    protected function processRelatedFields(array $class_fields, array $content): array
    {
        foreach ($content as $field => $value) {
            if (!isset($class_fields[$field])) { continue; }

            $field_type = $class_fields[$field]['type'];

            switch ($field_type) {
                case 'relation':
                    if ($this->shouldLoadCMSDetails()) {
                        break;
                    }
                    
                    if (!isset($class_fields[$field]['relation']) || !isset($class_fields[$field]['relation']['to'])) { 
                        $content[$field] = null;
                        break; 
                    }
                    if (empty($value)) {
                        $content[$field] = null;
                        break; 
                    }

                    $related_class = $class_fields[$field]['relation']['to'];
                    $RelatedModel = new $related_class();
                    $related_id = $this->getModelMorphableIdField($RelatedModel);

                    $content[$field] = $related_class::query()
                        ->where($related_id, $value)
                        ->first();
                    break;
            }
        }

        return $content;
    }

    /**
     * Process any castable fields within the stored block content
     * 
     * @param Model $model
     * @param string $key
     * @param int $sort_order
     * @param array $class_fields
     * @param array $content
     * @param string $cast_type
     * @return array
     */
    protected function processCastables(
        Model $model, 
        string $key, 
        int $sort_order,
        array $class_fields, 
        array $content, 
        string $cast_type
    ): array
    {
        foreach ($content as $field => $value) {
            if (!isset($class_fields[$field])) {
                continue;
            }

            $field_type = $class_fields[$field]['type'];
            $castable_class = isset($this->field_casts[$field_type]) ? $this->field_casts[$field_type] : null;
            $castable_value = $value;
            $castable_attrs = [];

            switch ($field_type) {
                case 'image':
                    $castable_attrs = [
                        'block_config'  => [
                            'fields'      => $class_fields,
                            'field_key'   => $field
                        ]
                    ];
                    break;
                case 'grouped':
                    $castable_attrs = [
                        'block_grouped_fields'  => $class_fields[$field]['fields']
                    ];
                    break;
                case 'orderable_select':
                    if (isset($class_fields[$field]['relation']) && isset($class_fields[$field]['relation']['to'])) {
                        $castable_class = RelatedOrderableSelect::class;
                        $castable_attrs = [
                            'block_related_class'   => $class_fields[$field]['relation']['to']
                        ];
                    }
                    break;
                case 'repeatable':
                case 'stacked_repeatable':
                    $castable_attrs = [
                        'block_repeatable_fields'   => $class_fields[$field]['fields'],
                        'block_repeatable_callback' => isset($class_fields[$field]['callback']) ? $class_fields[$field]['callback'] : null
                    ];
                    break;
                case 'theme_map':
                    $castable_attrs = [
                        'block_map_type' => $class_fields[$field]['map_type']
                    ];
                    break;
            }

            if (!is_null($castable_class) && class_exists($castable_class)) {
                $Castable = new $castable_class();
                if ($cast_type == 'set') {
                    $content[$field] = $Castable->set(
                        $model,
                        $key . '.' . $sort_order . '.content.' . $field,
                        $castable_value,
                        $castable_attrs
                    );
                } else {
                    $content[$field] = $Castable->get(
                        $model,
                        $key . '.' . $sort_order . '.content.' . $field,
                        $castable_value,
                        $castable_attrs
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Get the 'primary' morphable ID field for the provided model
     * 
     * @param Model $model
     * @return string
     */
    protected function getModelMorphableIdField(Model $model): string
    {
        if (method_exists($model, 'getMorphableIdField')) {
            return $model->getMorphableIdField();
        }

        $keys = $model->getKeyName();
        if (!is_array($keys)) { return $keys; }

        foreach ($keys as $key) {
            if (!in_array($key, ['language_id', 'version'])) {
                return $key;
            }
        }

        return reset($keys);
    }
}