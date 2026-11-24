<?php

namespace App\Models;

use App\Helpers\ImageHelper;
use App\Models\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use SoftDeletes, CachesQueries;

    protected $table        = 'files';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'file_folder_id',
        'storage_path',
        'file_path',
        'title',
        'alt_text',
        'extension',
        'width',
        'height'
    ];

    protected static $IMG_EXTS  = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

    protected static $EDITABLE_IMG_EXTS = ['jpg', 'jpeg', 'png'];

    /**
     * Relationship, returning the folder this file belongs to
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class, 'file_folder_id');
    }

    /**
     * Query scope, restricting returned files within the provided
     * folder
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $folder_id
     * @return void
     */
    #[Scope]
    protected function inFolder(Builder $query, int $folder_id): void
    {
        $query->where('files.file_folder_id', $folder_id);
    }

    /**
     * Query scope, restricting returned files to images only,
     * based on their extension
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    #[Scope]
    protected function imagesOnly(Builder $query): void
    {
        $query->whereIn('extension', self::$IMG_EXTS);
    }

    /**
     * Determines whether the file is an image or not (based on it's extension)
     * 
     * @return Attribute
     */
    protected function isImage(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => in_array($attributes['extension'], self::$IMG_EXTS)
        )->shouldCache();
    }

    /**
     * Determines wheether the file is editable (an image & config setting enabled)
     * 
     * @return Attribute
     */
    protected function isEditable(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => in_array($attributes['extension'], self::$EDITABLE_IMG_EXTS) && config('image.editable')
        )->shouldCache();
    }

    /**
     * Return the provided size in something human readable (with mb, gb, etc.)
     *
     * @param mixed $size       Number of bytes to format
     * @param int $precision    Number of decimal places to use
     * @return string
     */
    public static function formatBytes(mixed $size, $precision = 2): string
    {
        $base = log($size, 1024);
        $suffixes = array('b', 'kb', 'mb', 'gb', 'tb');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    /**
     * Return the file size in a human readable format
     *
     * @return Attribute
     */
    protected function filesize(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => self::formatBytes(Storage::size($attributes['storage_path']))
        )->shouldCache();
    }

    /**
     * Returning the image dimensions as width x height string.
     *
     * @return Attribute
     */
    protected function dimensions(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['width'] . 'x' . $attributes['height'] . 'px'
        )->shouldCache();
    }

    /**
     * Returns the image aspect ratio.
     * 
     * @return Attribute
     */
    protected function aspectRatio(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => ImageHelper::getAspectRatio($attributes['width'], $attributes['height'])
        )->shouldCache();
    }

    /**
     * Returns the file's full path, including the folder path
     * 
     * @return Attribute
     */
    protected function fullPath(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $folder_path = FileFolder::getFullPath($attributes['file_folder_id']);
                return ($folder_path ? $folder_path . '/' : '') . $attributes['file_path'];
            }
        )->shouldCache();
    }

    /**
     * Return a common identifier for a type of icon,
     * used when rendering tiles in the file manager
     * 
     * @return Attribute
     */
    protected function iconType(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $ext = strtolower($attributes['extension']);
                if (in_array($ext, ['mov', 'mp4', 'avi', 'webm', 'm4v', 'qt', 'wmv', 'flv', 'mpg', 'asf'])) {
                    return 'video';
                }
                if (in_array($ext, ['csv', 'xls', 'xlsx'])) {
                    return 'spreadsheet';
                }

                return 'default';
            }
        )->shouldCache();
    }

    /**
     * Determines whether the current file is previewable / renderable
     * in the browser
     * 
     * @return Attribute
     */
    protected function isFilePreviewable(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => !in_array(
                strtolower($attributes['extension']),
                [
                    'mov', 'avi', 'm4v', 'qt', 'wmv', 'flv', 'asf', // Non-web video
                    'xls', 'xlsx', 'doc', 'docx',   // Non-web documents
                ]
            )
        )->shouldCache();
    }

    /**
     * Determine whether the provided file path already exists or not
     * 
     * @param string $file_path     The file path to check
     * @param int $folder_id        The folder uploaded to
     * @param int $ignore_id        A file ID to ignore (i.e. if replacing)
     * @return bool
     */
    public static function checkDuplicateFilePath(string $file_path, int $folder_id, int $ignore_id = 0): bool
    {
        $Qry = self::where([
            'file_path'         => $file_path,
            'file_folder_id'    => $folder_id
        ]);
        if ($ignore_id) {
            $Qry->where('id', '<>', $ignore_id);
        }
        return $Qry->count() > 0;
    }

    /**
     * Query scope used to perform searches
     * 
     * @param Builder $query
     * @param string $search_term
     * @return void
     */
    #[Scope]
    protected function matchingSearchTerm(Builder $query, string $search_term): void
    {
        $query->where('title', 'LIKE', '%' . $search_term . '%');
    }
}