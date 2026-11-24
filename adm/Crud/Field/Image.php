<?php

namespace Adm\Crud\Field;

use Adm\Crud\CrudInterface;
use App\Helpers\ImageHelper;
use App\Models\AssignedSizedFile;
use App\Models\File;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class Image extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $sortable = false;

    /**
     * Image preview width
     * 
     * @var int
     */
    const PREVIEW_WIDTH     = 130;

    /**
     * Image preview height
     * 
     * @var int
     */
    const PREVIEW_HEIGHT    = 130;

    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $this->processSizes($details);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForListing(array $details): array
    {
        $this->processSizes($details);
        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForForm(array $details, ?Model $Item): array
    {
        $this->processSizes($details);
        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        data_set($Item, $field, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        if (!is_array($value)) {
            return;
        }
        
        $this->checkModifySizes($details);
        foreach ($value as $size => $size_details) {
            if (isset($details['sizes'][$size])) {
                $config_size = $details['sizes'][$size];
                $crop = [
                    'w' => isset($size_details['crop_w']) ? $size_details['crop_w'] : -1,
                    'h' => isset($size_details['crop_h']) ? $size_details['crop_h'] : -1,
                    'y' => isset($size_details['crop_y']) ? $size_details['crop_y'] : -1,
                    'x' => isset($size_details['crop_x']) ? $size_details['crop_x'] : -1
                ];
                $this->storeAssignedFile(
                    $Item,
                    $field,
                    $size,
                    $size_details['file_id'],
                    $config_size['width'],
                    $config_size['height'],
                    $crop,
                    isset($config_size['style']) ? $config_size['style'] : null
                );
            }
        }
    }

    /**
     * Stores the found file ID & size configuration against the
     * item in the AssignedSizedFiles table
     * 
     * @param Model $Item
     * @param string $field
     * @param string $size
     * @param ?int $file_id
     * @param ?int $width
     * @param ?int $height
     * @param ?array $crop
     * @param ?string $style
     * @return void
     */
    protected function storeAssignedFile(
        Model $Item, 
        string $field, 
        string $size, 
        ?int $file_id, 
        ?int $width, 
        ?int $height, 
        ?array $crop, 
        ?string $style
    ): void
    {
        if (empty($file_id)) { return; }

        $ConfigClass = $this->CrudInterface->getConfigClass();
        AssignedSizedFile::updateOrCreate(
            [
                'morphable_id'      => data_get($Item, $this->CrudInterface->getClassIDField()),
                'morphable_type'    => $ConfigClass->getStaticPropertyValue('class'),
                'morphable_field'   => $field,
                'language_id'       => data_get($Item, 'language_id'),
                'version'           => data_get($Item, 'version', 1),
                'size'              => $size
            ],
            [
                'file_id'       => $file_id,
                'width'         => $width,
                'height'        => $height,
                'render_style'  => $style,
                'crop_w'        => $crop['w'],
                'crop_h'        => $crop['h'],
                'crop_x'        => $crop['x'],
                'crop_y'        => $crop['y'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function afterDeleted(Model $Item, string $field, array $details): void
    {
        $ConfigClass = $this->CrudInterface->getConfigClass();
        AssignedSizedFile::where([
            'morphable_id'      => data_get($Item, $this->CrudInterface->getClassIDField()),
            'morphable_type'    => $ConfigClass->getStaticPropertyValue('class'),
            'morphable_field'   => $field,
            'language_id'       => data_get($Item, 'language_id')
        ])->delete();
    }

    /**
     * Process the assigned image sizes, ready for output
     * in the CMS
     * 
     * @param array $field_details
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function processSizes(array &$field_details): void
    {
        if (!isset($field_details['sizes'])) {
            throw new InvalidArgumentException('No image sizes found for \'' . $field_details['label'] . '\'.');
        }
        if (empty($field_details['sizes'])) {
            throw new InvalidArgumentException('At least 1 image size needs to be set for \'' . $field_details['label'] . '\'.');
        }

        $this->checkModifySizes($field_details);

        $listing_size = null;
        foreach ($field_details['sizes'] as $key => $size_detail) {
            if (isset($size_detail['cms_hide']) && $size_detail['cms_hide']) {
                unset($field_details['sizes'][$key]);
                continue;
            }

            if (isset($size_detail['use_for_listing']) && $size_detail['use_for_listing']) {
                $listing_size = $key;
            }

            $dims = [
                $size_detail['width'] ? $size_detail['width'] : 'Any',
                $size_detail['height'] ? $size_detail['height'] : 'Any',
            ];
            $field_details['sizes'][$key]['dimensions'] = implode(' x ', $dims) . 'px';

            if (
                isset($size_detail['fallback']) && 
                !empty($size_detail['fallback']) &&
                isset($field_details['sizes'][$size_detail['fallback']])
            ) {
                $field_details['sizes'][$key]['fallback_label'] = $field_details['sizes'][$size_detail['fallback']]['label'];
            }
        }
        if (!$listing_size) {
            $keys = array_keys($field_details['sizes']);
            $listing_size = reset($keys);
        }
        $field_details['listing_size'] = $listing_size;
    }

    /**
     * Checks the provided field details for a modification
     * callback, and calls it where appropriate
     * 
     * @param array $field_details
     * @return void
     */
    protected function checkModifySizes(array &$field_details): void
    {
        if (isset($field_details['modify_sizes_callback'])) {
            $method = $field_details['modify_sizes_callback'];
            $ConfigClass = $this->CrudInterface->getConfigClass();
            if ($ConfigClass->hasMethod($method)) {
                $field_details['sizes'] = $ConfigClass->getMethod($method)->invoke(null, $field_details['sizes'], $this->CrudInterface->getCurrentItemId());
            }
        }
    }

    /**
     * Returns a JSON response for the requested image, ready
     * for output in the CMS
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \InvalidArgumentException
     */
    public function getImageDetailsForRender(): JsonResponse
    {
        $file = request()->input('file');
        $field = request()->input('field');
        $size = request()->input('size');
        $details = request()->input('details');
        if (!$file || !$field || !$size || !$details) {
            throw new InvalidArgumentException('The request was missing either file, field, size or details.');
        }

        try {
            $details = json_decode($details, true);
        } catch (Exception $e) {
            throw new InvalidArgumentException('JSON details could not be parsed.');
        }
        
        $SelectedFile = File::find($file);
        if (!$SelectedFile) {
            throw new InvalidArgumentException('Selected file not found. Has the request been altered?');
        }

        $src = ImageHelper::getImageUrlForMaxDimensions($SelectedFile, self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT);
        list($width, $height) = ImageHelper::getImageWidthAndHeightWhenFitted($SelectedFile, self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT);
        return response()->json([
            'file_id'       => $SelectedFile->id,
            'folder_id'     => $SelectedFile->folder ? $SelectedFile->file_folder_id : 0,
            'folder_title'  => $SelectedFile->folder ? $SelectedFile->folder->title : 'Site files',
            'image'         => [
                'src'           => $src,
                'alt'           => $SelectedFile->alt_text,
                'width'         => $width,
                'height'        => $height
            ]
        ]);

        return response()->json(request()->all());
    }

    /**
     * Returns details for the requested image, ready for use
     * in the edit/crop file modal
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \InvalidArgumentException
     */
    public function getImageCropModal(): JsonResponse
    {
        $file = request()->input('file');
        $field = request()->input('field');
        $size = request()->input('size');
        $details = request()->input('details');
        if (!$file || !$field || !$size || !$details) {
            throw new InvalidArgumentException('The request was missing either file, field, size or details.');
        }

        try {
            $details = json_decode($details, true);
        } catch (Exception $e) {
            throw new InvalidArgumentException('JSON details could not be parsed.');
        }

        $SelectedFile = File::find($file);
        if (!$SelectedFile) {
            throw new InvalidArgumentException('Selected file not found. Has the request been altered?');
        }

        return response()->json([
            'success'   => true,
            'src'       => route('image', $SelectedFile->full_path),
            'width'     => $SelectedFile->width,
            'height'    => $SelectedFile->height
        ]);
    }

    /**
     * Returns a JSON response containing the src attribute
     * for the cropped image details provided
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \InvalidArgumentException
     */
    public function getCroppedImageSource(): JsonResponse
    {
        $file = request()->input('file');
        $field = request()->input('field');
        $size = request()->input('size');
        $details = request()->input('details');
        $crop = request()->input('crop');
        if (!$file || !$field || !$size || !$details || !$crop) {
            throw new InvalidArgumentException('The request was missing either file, field, size, details or crop.');
        }

        try {
            $details = json_decode($details, true);
            $crop = json_decode($crop, true);
        } catch (Exception $e) {
            throw new InvalidArgumentException('JSON details or crop could not be parsed.');
        }

        $SelectedFile = File::find($file);
        if (!$SelectedFile) {
            throw new InvalidArgumentException('Selected file not found. Has the request been altered?');
        }

        $SizedFile = new AssignedSizedFile();
        $SizedFile->file_id = $SelectedFile->id;
        $SizedFile->width = $details['width'];
        $SizedFile->height = $details['height'];
        $SizedFile->render_style = $details['style'];
        $SizedFile->crop_w = $crop['width'];
        $SizedFile->crop_h = $crop['height'];
        $SizedFile->crop_x = $crop['x'];
        $SizedFile->crop_y = $crop['y'];

        return response()->json([
            'success'   => true,
            'src'       => $SizedFile->src
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        // Do nothing
    }
}