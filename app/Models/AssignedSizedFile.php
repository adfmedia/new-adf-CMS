<?php

namespace App\Models;

use App\Models\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignedSizedFile extends Model
{
    use SoftDeletes, CachesQueries;

    protected $table        = 'assigned_sized_files';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'morphable_id',         // ID of linked resource (e.g. page_id)
        'morphable_type',       // Type of linked response (e.g. page)
        'morphable_field',      // Field the file is assigned to
        'language_id',
        'version',
        'size',
        'file_id',
        'width',
        'height',
        'render_style',
        'crop_w',
        'crop_h',
        'crop_x',
        'crop_y',
    ];
    
    /**
     * Return the file record assigned to this 'sized' file
     * 
     * @return HasOne
     */
    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    /**
     * Return the 'src' attribute for this 'sized' file, used
     * in <img> tags
     * 
     * @return Attribute
     */
    protected function src(): Attribute
    {
        $File = $this;
        return Attribute::make(
            get: function (mixed $value) use ($File) {
                if (!$File->file) {
                    return '';
                }

                $src = $File->getSrcForSizeAndStyle(
                    $File->file->full_path,
                    $File->width,
                    $File->height,
                    $File->crop,
                    $File->render_style
                );

                $suffix = strtotime($File->file->created_at) < strtotime($File->file->updated_at) ? strtotime($File->file->updated_at) : '';
                if (!empty($suffix)) {
                    $src .= '?' . $suffix;
                }

                return $src;
            }
        )->shouldCache();
    }

    /**
     * Return the crop dimensions for this 'sized' file
     * 
     * @return Attribute
     */
    protected function crop(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $crop = null;
                if ($attributes['crop_w'] > -1 && $attributes['crop_h'] > -1) {
                    $crop = $attributes['crop_w'] . 'x' . $attributes['crop_h'];

                    if ($attributes['crop_x'] > -1 || $attributes['crop_y'] > -1) {
                        $crop .= '-' . $attributes['crop_x'] . 'x' . $attributes['crop_y'];
                    }
                }
                if ($crop == '0x0-0x0') {
                    $crop = null;
                }
                return $crop;
            }
        )->shouldCache();
    }

    /**
     * Return the route path for this 'sized' file, based on
     * it's resize & crop dimensions, and fill/fit style
     * 
     * @param string $file_path     Image file path
     * @param int $width            Resize width
     * @param int $height           Resize height
     * @param ?string $crop         Crop dimensions to apply
     * @param string $style         Fill/fit style
     * @return string
     */
    public function getSrcForSizeAndStyle(string $file_path, int $width, int $height, ?string $crop, string $style = 'fill'): string
    {
        $size_route = 'image.fit';
        $crop_route = 'image.fit.crop';
        switch ($style) {
            case 'fill':
                $size_route = 'image.fill';
                $crop_route = 'image.fill.crop';
                break;
            case 'fit-in':
                $size_route = 'image.fit-in';
                $crop_route = 'image.fit-in.crop';
                break;
        }

        $dims = $width . 'x' . $height;
        $crop = $crop;

        if ($crop) {
            return route($crop_route, [
                'path'          => $file_path,
                'dimensions'    => $dims,
                'crop'          => $crop
            ]);
        }

        return route($size_route, [
            'path'          => $file_path,
            'dimensions'    => $dims
        ]);
    }

    /**
     * Return the 'sized' file's fill/fit style
     * 
     * @return Attribute
     */
    public function renderStyle(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => (!is_null($value) ? $value : 'fill')
        );
    }
}