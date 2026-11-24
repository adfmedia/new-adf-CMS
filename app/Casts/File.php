<?php

namespace App\Casts;

use App\Models\File as FileModel;
use Illuminate\Database\Eloquent\Model;

class File extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        $File = FileModel::find($value);
        if (!$File) {
            return [];
        }

        return [
            'file_id'   => $File->id,
            'file_path' => route('file', $File->full_path),
            'download_path' => route('file', ['path' => $File->file_path, 'download']),
            'folder_details'    => [
                'id'    => $File->folder ? $File->file_folder_id : 0,
                'title' => $File->folder ? $File->folder->title : 'Site files'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * 
     * @return int
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        // We just need one of the values to store against the record (in case it's not nullable)
        if (is_array($value) && isset($value['file_id'])) {
            return $value['file_id'];
        }
        
        return 0;
    }
}