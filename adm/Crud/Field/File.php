<?php

namespace Adm\Crud\Field;

use App\Models\File as FileModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class File extends Field
{
    /**
     * Return a JSON response of details about the file requested
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \InvalidArgumentException
     */
    public function getFileDetailsForRender(): JsonResponse
    {
        $file = request()->input('file');
        if (!$file) {
            throw new InvalidArgumentException('The request was missing the selected file.');
        }

        $File = FileModel::find($file);
        if (!$File) {
            throw new InvalidArgumentException('Selected file not found. Has the request been altered?');
        }

        return response()->json([
            'file_id'       => $File->id,
            'file_path'     => route('file', $File->full_path),
            'folder_id'     => $File->folder ? $File->file_folder_id : 0,
            'folder_title'  => $File->folder ? $File->folder->title : 'Site files',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        $file_ids = FileModel::where('title', 'LIKE', '%' . $search_term . '%')
            ->select('id')->get()->pluck('id')->toArray();
        $query->orWhereIn($table . '.' . $field, $file_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(Builder $query, string $table, string $field_name, string $sort_dir, array $field_details): void
    {
        $FModel = new FileModel();
        $ftable = $FModel->getTable();
        $query->join($ftable, $ftable . '.id', '=', $table . '.' . $field_name)
            ->orderBy($ftable . '.title', $sort_dir);
    }
}