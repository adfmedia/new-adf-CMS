<?php

namespace App\Casts;

use Adm\Crud\Field\Image AS CmsImageField;
use App\Helpers\ImageHelper;
use App\Models\AssignedSizedFile;
use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class Image extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return $this->getWithCache(
            $model, $key, $value, $attributes, 
            function () use ($model, $key, $value, $attributes) {
                $AssignedFiles = AssignedSizedFile::withTrashed()
                    ->where([
                        'morphable_id'      => $model->{$this->getModelMorphableIdField($model)},
                        'morphable_type'    => get_class($model),
                        'morphable_field'   => $key,
                        'language_id'       => $model->language_id,
                        'version'           => $model->version,
                    ])->get();

                // Convert assigned files into size based array
                $size_arr = [];
                /** @var AssignedSizedFile $AssignedFile */
                foreach ($AssignedFiles as $AssignedFile) {
                    // Don't include the size if the file no longer exists
                    if (!$AssignedFile->file) { continue; }

                    $details = [
                        // For FE
                        'file_id'   => $AssignedFile->file_id,
                        'alt'       => $AssignedFile->file->alt_text,
                        'src'       => $AssignedFile->src,
                        'width'     => $AssignedFile->width,
                        'height'    => $AssignedFile->height
                    ];

                    // Only load additional details for the CMS if required
                    if ($this->shouldLoadCMSDetails()) {
                        list($cms_src, $cms_w, $cms_h) = $this->getCmsSrcDetailsForImage($AssignedFile->file, $AssignedFile->width, $AssignedFile->height, $AssignedFile->crop, $AssignedFile->render_style);
                        $cms_details = [
                            // For CMS
                            'ratio'             => ImageHelper::getWidthToHeightRatio($AssignedFile->width, $AssignedFile->height),
                            'crop_w'            => $AssignedFile->crop_w,
                            'crop_h'            => $AssignedFile->crop_h,
                            'crop_x'            => $AssignedFile->crop_x,
                            'crop_y'            => $AssignedFile->crop_y,
                            'folder_details'    => [
                                'id'    => $AssignedFile->file->folder ? $AssignedFile->file->file_folder_id : 0,
                                'title' => $AssignedFile->file->folder ? $AssignedFile->file->folder->title : 'Site files'
                            ],
                            'preview_image'     => [
                                'src'       => $cms_src,
                                'width'     => $cms_w,
                                'height'    => $cms_h
                            ],
                        ];

                        $details = array_merge($details, $cms_details);
                    } else {
                        $details = $this->calculateImageDimensions($AssignedFile, $details);
                    }

                    $size_arr[$AssignedFile->size] = $details;
                }

                // Only load fallback images outside of the CMS
                if (!$this->shouldLoadCMSDetails()) {
                    $block_config = [];
                    if (isset($attributes['block_config'])) { $block_config = $attributes['block_config']; }
                    $size_arr = $this->loadFallbacks($model, $key, $size_arr, $block_config);
                }

                return $size_arr;
            }
        );
    }

    /**
     * {@inheritdoc}
     * 
     * @return int
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if (!is_array($value)) { return (int)$value; }
        
        // We just need one of the values to store against the record (in case it's not nullable)
        foreach ($value as $size_details) {
            if (!empty($size_details['file_id'])) {
                return (int)$size_details['file_id'];
            }
        }
        
        return 0;
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

    /**
     * Return an array of details to use for the provided image in the CMS
     * 
     * @param File $File
     * @param int $width
     * @param int $height
     * @param mixed $crop
     * @param mixed $style
     * @return array
     */
    protected function getCmsSrcDetailsForImage(File $File, int $width, int $height, mixed $crop, mixed $style): array
    {
        $size_route = $style == 'fill' ? 'image.fill' : 'image.fit';
        $crop_route = $style == 'fill' ? 'image.fill.crop' : 'image.fit.crop';

        $pw = CmsImageField::PREVIEW_WIDTH;
        $ph = CmsImageField::PREVIEW_HEIGHT;

        if ($style == 'fill') {
            list($target_w, $target_h) = ImageHelper::getImageWidthAndHeightWhenFilled($File, $width, $height, $pw, $ph);
        } else {
            list($target_w, $target_h) = ImageHelper::getImageWidthAndHeightWhenFitted($File, $pw, $ph);
        }

        $dims = $target_w . 'x' . $target_h;
        if ($crop) {
            $url = route($crop_route, ['path' => $File->full_path, 'dimensions' => $dims, 'crop' => $crop]);
        } else {
            $url = route($size_route, ['path' => $File->full_path, 'dimensions' => $dims]);
        }
        return [$url, $target_w, $target_h];
    }

    /**
     * Loads any fallback images into the sizes array
     * 
     * @param Model $model
     * @param string $key
     * @param array $stored_sizes
     * @param array $block_config
     * @return array
     */
    protected function loadFallbacks(Model $model, string $key, array $stored_sizes, array $block_config = []): array
    {
        $fields         = [];
        $sizes          = [];
        $app_only_sizes = [];
        if (!empty($block_config) && isset($block_config['fields']) && isset($block_config['field_key'])) {
            $fields = $block_config['fields'];
            $field_key = $block_config['field_key'];
            if (isset($fields[$field_key]) && isset($fields[$field_key]['sizes'])) {
                $sizes = $fields[$field_key]['sizes'];
            }
            if (isset($fields[$field_key]) && isset($fields[$field_key]['app_only_sizes'])) {
                $app_only_sizes = $fields[$field_key]['app_only_sizes'];
            }
        } 
        if (empty($fields) && isset($model->config_class)) {
            $Conf = new ReflectionClass($model->config_class);
            $fields = $Conf->getStaticPropertyValue('fields');
            if (isset($fields[$key]) && isset($fields[$key]['sizes'])) {
                $sizes = $fields[$key]['sizes'];
            }
            if (isset($fields[$key]) && isset($fields[$key]['app_only_sizes'])) {
                $app_only_sizes = $fields[$key]['app_only_sizes'];
            }
        }

        if (!empty($sizes)) {
            $stored_sizes = $this->injectFallbackSizes($stored_sizes, $sizes);
        }
        if (!empty($app_only_sizes)) {
            $stored_sizes = $this->injectFallbackSizes($stored_sizes, $app_only_sizes);
        }

        return $stored_sizes;
    }

    /**
     * Actually performs the fallback size addition as per loadFallbacks()
     * 
     * @param array $stored_sizes
     * @param array $sizes
     * @return array
     */
    protected function injectFallbackSizes(array $stored_sizes, array $sizes): array
    {
        $AssignedFileRef = new AssignedSizedFile();
        foreach ($sizes as $size_key => $size_details) {
            if (isset($stored_sizes[$size_key])) {
                continue;
            }
            if (!isset($size_details['fallback'])) {
                continue;
            }

            $fb_key = $size_details['fallback'];
            if (isset($stored_sizes[$fb_key])) {
                $File = File::find($stored_sizes[$fb_key]['file_id']);

                $stored_sizes[$size_key] = [
                    'file_id'   => $File->id,
                    'alt'       => $File->alt_text,
                    'src'       => $AssignedFileRef->getSrcForSizeAndStyle($File->full_path, $size_details['width'], $size_details['height'], null, (isset($size_details['style']) ? $size_details['style'] : 'fill')),
                    'width'     => $size_details['width'],
                    'height'    => $size_details['height']
                ];
            }
        }

        return $stored_sizes;
    }

    /**
     * Dynamically calculate an image's resized dimensions
     * when the configuration only include width or height,
     * returning the original array populated with sizes
     * 
     * @param AssignedSizedFile $AssignedFile
     * @param array $details
     * @return array
     */
    protected function calculateImageDimensions(AssignedSizedFile $AssignedFile, array $details): array
    {
        if ($details['height'] == 0 && $details['width'] > 0) {
            // Calculate height based on ratio
            $ratio = $AssignedFile->file->height / ($AssignedFile->file->width ? $AssignedFile->file->width : 1);
            $details['height'] = round($details['width'] * $ratio);
        } elseif ($details['width'] == 0 && $details['height'] > 0) {
            // Calculate width based on ratio
            $ratio = $AssignedFile->file->width / ($AssignedFile->file->height ? $AssignedFile->file->height : 1);
            $details['width'] = round($details['height'] * $ratio);
        }

        return $details;
    }
}
