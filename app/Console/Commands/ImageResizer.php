<?php

namespace App\Console\Commands;

use App\Helpers\ClassFinder;
use App\Models\AssignedSizedFile;
use App\Models\PageBuilderBlock;
use Illuminate\Console\Command;
use ReflectionClass;

class ImageResizer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature    = 'images:resize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description  = 'Updates any assigned images to set new width, height & style properties based on configuration files';

    /**
     * A local map of model class names to config class names
     * 
     * @var array
     */
    protected $config_file_map  = [];

    /**
     * Execute the command
     * 
     * @return void
     */
    public function handle(): void
    {
        $AssignedImages = AssignedSizedFile::get();
        foreach ($AssignedImages as $Image) {
            $ModelConfig = $this->getConfigClass($Image->morphable_type);
            if (!$ModelConfig) {
                continue;
            }

            if (stripos($Image->morphable_field, 'page_builder.') === 0) {
                $this->updatePageBuilderImage($Image);
            } else {
                $this->updateModelImage($Image, $ModelConfig);
            }
        }
    }

    /**
     * Returns a reflection class of the provided model's config class if found
     * or null if no config can be determined
     * 
     * @param string $morphable_class
     * @return ?ReflectionClass
     */
    protected function getConfigClass(string $morphable_class): ?ReflectionClass
    {
        if (isset($this->config_file_map[$morphable_class])) {
            return $this->config_file_map[$morphable_class];
        }

        $ConfigClass = ClassFinder::findConfigClassForModel($morphable_class);
        if (!$ConfigClass) {
            return null;
        }

        $this->config_file_map[$morphable_class] = $ConfigClass;
        return $ConfigClass;
    }

    /**
     * Update the provided image with the updated width, height & render style from the
     * configuration class's field settings (if needed)
     * 
     * @param AssignedSizedFile $Image
     * @param ReflectionClass $ConfigClass
     * @return void
     */
    protected function updateModelImage(AssignedSizedFile $Image, ReflectionClass $ConfigClass): void
    {
        $field_definitions = $ConfigClass->getStaticPropertyValue('fields');
        if (!isset($field_definitions[$Image->morphable_field]) || !isset($field_definitions[$Image->morphable_field]['sizes'])) {
            return;
        }

        $image_sizes = $field_definitions[$Image->morphable_field]['sizes'];
        if (!isset($image_sizes[$Image->size])) {
            return;
        }

        $this_size = $image_sizes[$Image->size];
        if (
            $this_size['width'] != $Image->width ||
            $this_size['height'] != $Image->height ||
            (isset($this_size['style']) && $this_size['style'] != $Image->render_style)
        ) {
            $Image->width = $this_size['width'];
            $Image->height = $this_size['height'];
            if (isset($this_size['style'])) {
                $Image->render_style = $this_size['style'];
            }
            $Image->save();
        }
    }

    /**
     * Update the provided page builder image with the updated width, height & render style from the
     * block's field settings (if needed)
     * 
     * @param AssignedSizedFile $Image
     * @return void
     */
    protected function updatePageBuilderImage(AssignedSizedFile $Image): void
    {
        $block_parts = explode('.', $Image->morphable_field);
        $field = array_shift($block_parts);
        $index = array_shift($block_parts);
        $content_str = array_shift($block_parts);
        $target_field = implode('.', $block_parts);

        $Block = PageBuilderBlock::where([
            'morphable_id'      => $Image->morphable_id,
            'morphable_type'    => $Image->morphable_type,
            'morphable_field'   => $field,
            'language_id'       => $Image->language_id,
            'version'           => $Image->version,
            'sort_order'        => $index
        ])->first();
        if (!$Block) {
            return;
        }

        $block_class_name = $Block->block_class;
        if (!class_exists($block_class_name)) {
            return;
        }

        $fields = $block_class_name::$fields;
        if (stristr($target_field, '.') === false) {
            // Regular field
            if (
                !isset($fields[$target_field]) ||
                !isset($fields[$target_field]['sizes']) ||
                !isset($fields[$target_field]['sizes'][$Image->size])
            ) {
                return;
            }

            $this_size = $fields[$target_field]['sizes'][$Image->size];
            if (
                $this_size['width'] != $Image->width ||
                $this_size['height'] != $Image->height ||
                (isset($this_size['style']) && $this_size['style'] != $Image->render_style)
            ) {
                $Image->width = $this_size['width'];
                $Image->height = $this_size['height'];
                if (isset($this_size['style'])) {
                    $Image->render_style = $this_size['style'];
                }
                $Image->save();
            }
        } else {
            // Nested field (e.g. repeatable)
            $content_field_parts = explode('.', $target_field);
            $sub_field = array_shift($content_field_parts);
            if (!isset($fields[$sub_field]) || !isset($fields[$sub_field]['fields'])) {
                return;
            }

            $sub_idx = array_shift($content_field_parts);
            $sub_sub_field = array_shift($content_field_parts);
            if (
                !isset($fields[$sub_field]['fields'][$sub_sub_field]) ||
                !isset($fields[$sub_field]['fields'][$sub_sub_field]['sizes']) ||
                !isset($fields[$sub_field]['fields'][$sub_sub_field]['sizes'][$Image->size])
            ) {
                return;
            }

            $this_size = $fields[$sub_field]['fields'][$sub_sub_field]['sizes'][$Image->size];
            if (
                $this_size['width'] != $Image->width ||
                $this_size['height'] != $Image->height ||
                (isset($this_size['style']) && $this_size['style'] != $Image->render_style)
            ) {
                $Image->width = $this_size['width'];
                $Image->height = $this_size['height'];
                if (isset($this_size['style'])) {
                    $Image->render_style = $this_size['style'];
                }
                $Image->save();
            }
        }
    }
}
