<?php

namespace App\Models;

use App\Models\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileFolder extends Model
{
    use SoftDeletes, CachesQueries;

    protected $table        = 'file_folders';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'parent_id',
        'path',
        'title',
    ];

    /**
     * Return the parent folder
     * 
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    /**
     * Return child folders
     * 
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    /**
     * Returns files in the folder
     * 
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Returns the full URL path for the folder
     * 
     * @param int $folder_id
     * @return string
     */
    public static function getFullPath(int $folder_id): string
    {
        $Folder = self::find($folder_id);
        if (!$Folder) { return ''; }

        $path_parts = [];
        self::hierarchicalPath($Folder, $path_parts);
        $path_parts = array_reverse($path_parts);
        return implode('/', $path_parts);
    }

    /**
     * Recursively generate an array containing the hierarchical 
     * path for the folder
     * 
     * @param FileFolder $Folder
     * @param array $paths
     * @return void
     */
    public static function hierarchicalPath(FileFolder $Folder, array &$paths): void
    {
        $paths[] = $Folder->path;
        if ($Folder->parent) {
            self::hierarchicalPath($Folder->parent, $paths);
        }
    }

    /**
     * For the provided array of path slugs, find the matching folder
     * 
     * @param array $path_parts
     * @param int $parent
     * @return ?FileFolder
     */
    public static function getFolderFromPathParts(array $path_parts, int $parent = 0): ?FileFolder
    {
        if (empty($path_parts)) { return null; }

        $folder_path = array_shift($path_parts);
        $CurrentFolder = self::where([
            'path'      => $folder_path,
            'parent_id' => $parent
        ])->first();
        if (!$CurrentFolder) { return null; }

        if (empty($path_parts)) { return $CurrentFolder; }
        return self::getFolderFromPathParts($path_parts, $CurrentFolder->id);
    }

    /**
     * Query scope used for searching folders
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
