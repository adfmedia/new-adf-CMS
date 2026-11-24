<?php

namespace Adm\Library\Import\Helpers;

use App\Models\File;
use App\Models\FileFolder;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Helper class for common functionality used during
 * file imports
 */
class FileImportHelper
{
    /**
     * Import a file from the provided URL into the local storage
     * system, returning the storage path
     * 
     * @param string $file_url  The URL to import from
     * @param string $ext       The file extension
     * @return string           The local storage path of the imported file
     * @throws \Exception
     */
    public function importFileFromUrl(string $file_url, string $ext): string
    {
        try {
            $file_contents = file_get_contents($file_url);
        } catch (Exception $e) {
            throw new Exception('File failed to retrieve: ' . $e->getMessage());
        }

        $storage_name = Str::random(40) . '.' . $ext;
        Storage::put($storage_name, $file_contents);

        return $storage_name;
    }

    /**
     * Check if a file exists by name in a directory
     * 
     * @param string $file_name     The file name to check
     * @param string $directory     The directory to check within
     * @return bool
     */
    public function fileExistsInDirectory(string $file_name, string $directory): bool
    {
        $File = $this->getFileInDirectory($file_name, $directory);
        return $File ? true : false;
    }

    /**
     * Returns a file instance if it exists by name in a directory,
     * null if it doesn't
     * 
     * @param string $file_name     The file name to retrieve
     * @param string $directory     The directory to check within
     * @return ?\App\Models\File
     */
    public function getFileInDirectory(string $file_name, string $directory): ?File
    {
        $Dir = $this->getDirectoryFromPath($directory);
        return File::where([
            'file_folder_id'    => $Dir->id,
            'title'             => $file_name
        ])->first();
    }

    /**
     * Create a new file in a directory, referencing the local
     * storage path
     * 
     * @param string $storage_name  Where the file is stored locally
     * @param string $file_name     The name of the file to create
     * @param string $directory     The directory to create the file in
     * @return \App\Models\File     The newly created file
     */
    public function createFileInDirectory(string $storage_name, string $file_name, string $directory): File
    {
        $Dir = $this->getDirectoryFromPath($directory);
        
        $ext = pathinfo($storage_name, PATHINFO_EXTENSION);
        $dims = [];
        try {
            $dims = getimagesize(Storage::path($storage_name));
        } catch (Exception $e) {}

        return File::create([
            'file_folder_id'    => $Dir->id,
            'storage_path'      => $storage_name,
            'file_path'         => Str::slug($file_name) . '.' . $ext,
            'title'             => $file_name,
            'alt_text'          => $file_name,
            'extension'         => $ext,
            'width'             => isset($dims[0]) ? (int)$dims[0] : 0,
            'height'            => isset($dims[1]) ? (int)$dims[1] : 0
        ]);
    }

    /**
     * Return a human-friendly file name based on the provided
     * URL
     * 
     * @param string $file_url  The URL to convert to a name
     * @return string           The sanitised file name
     */
    public function getFileNameFromUrl(string $file_url): string
    {
        $relative_url = parse_url($file_url, PHP_URL_PATH);
        $path_parts = explode('/', $relative_url);
        $file_name = end($path_parts);
        if (stripos($file_name, '.') > 0) {
            $file_name = substr($file_name, 0, stripos($file_name, '.'));
        }
        return str_replace('-', ' ', $file_name);
    }

    /**
     * Return a file system directory record based on the provided
     * path
     * 
     * @param string $path              The folder path, separated with slashes
     * @return \App\Models\FileFolder   The matched folder record
     */
    protected function getDirectoryFromPath(string $path): FileFolder
    {
        $TopLevelDir = new FileFolder([
            'id'    => 0
        ]);

        if ($path == '') {
            return $TopLevelDir;
        }

        $parts = explode('/', $path);
        $Dir = null;
        $parent_id = 0;
        while (!empty($parts)) {
            $name = urldecode(array_shift($parts));
            $Dir = $this->getDirectory($name, $parent_id);
            if ($Dir) {
                $parent_id = $Dir->id;
            }
        }

        return $Dir ? $Dir : $TopLevelDir;
    }

    /**
     * Return a file system directory record based on it's name
     * and parent directory - if it doesn't exist, create it
     * 
     * @param string $name              The directory name
     * @param int $parent_id            The parent directory ID
     * @return \App\Models\FileFolder   The located directory, or a newly created one
     */
    protected function getDirectory(string $name, int $parent_id): FileFolder
    {
        $Dir = FileFolder::where([
            'title'     => $name,
            'parent_id' => $parent_id
        ])->first();
        
        return $Dir ? $Dir : $this->createDirectory($name, $parent_id);
    }

    /**
     * Create a new file system directory with the provided name
     * in the provided parent directory
     * 
     * @param string $name              The directory name to create
     * @param int $parent_id            The parent directory ID to assign
     * @return \App\Models\FileFolder   The newly created directory
     */
    protected function createDirectory(string $name, int $parent_id): FileFolder
    {
        return FileFolder::create([
            'parent_id' => $parent_id,
            'path'      => Str::slug($name),
            'title'     => $name
        ]);
    }
}