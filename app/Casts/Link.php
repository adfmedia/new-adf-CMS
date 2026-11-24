<?php

namespace App\Casts;

use App\Models\File;
use App\Models\FileFolder;
use App\Models\Url;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Link extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        $stored_link = Str::isJson($value) ? json_decode($value, true) : (is_array($value) ? $value : []);

        if (data_get($stored_link, 'type') && data_get($stored_link, 'value')) {
            $target = ' target="_blank"';
            switch (data_get($stored_link, 'type')) {
                case 'internal':
                    $UrlRecord = Url::find(data_get($stored_link, 'value'));
                    data_set($stored_link, 'value', $UrlRecord ? route('theme', $UrlRecord->url) . data_get($stored_link, 'suffix') : '');
                    $target = null;
                    break;
                case 'file':
                    $FileRecord = File::find(data_get($stored_link, 'value'));
                    data_set($stored_link, 'value', $FileRecord ? route('file', $FileRecord->full_path) : '');
                    break;
            }

            if (substr(data_get($stored_link, 'value', ''), 0, 1) == '#') {
                $target = null;
            }

            data_set($stored_link, 'url', data_get($stored_link, 'value'));
            data_set($stored_link, 'target', $target);
        }

        return $stored_link;
    }

    /**
     * {@inheritdoc}
     * 
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $storage_details = [
            'type'  => '',
            'value' => ''
        ];

        if (!empty($value) && !empty($value['value'])) {
            $link_to_store = $value['value'];
            $base_url = route('theme');
            if (stripos($link_to_store, $base_url) === 0) {
                $test = uniqid();
                $file_url = str_replace($test, '', route('file', $test));
                if (stripos($link_to_store, $file_url) === 0) {
                    $storage_details['type'] = 'file';
                    
                    $full_path = ltrim(str_replace($file_url, '', $link_to_store), '/');
                    $path_parts = explode('/', $full_path);
                    $file_path = array_pop($path_parts);
                    $ParentFolder = FileFolder::getFolderFromPathParts($path_parts);

                    $FileRecord = File::where([
                        'file_path'         => $file_path,
                        'file_folder_id'    => $ParentFolder ? $ParentFolder->id : 0
                    ])->first();

                    $storage_details['value'] = $FileRecord ? $FileRecord->id : null;
                } else {
                    $relative_link = ltrim(str_replace($base_url, '', $link_to_store), '/');
                    $split_pos = strcspn($relative_link, '?#');
                    $search_slug = substr($relative_link, 0, $split_pos);
                    $suffix = substr($relative_link, $split_pos);

                    $UrlRecord = Url::where('url', $search_slug)->first();
                    if ($base_url == $link_to_store && !$UrlRecord) {
                        $UrlRecord = Url::where('url', '/')->first();
                    }

                    if ($UrlRecord) {
                        $storage_details['type'] = 'internal';
                        $storage_details['value'] = $UrlRecord->id;
                        if ($suffix !== '') {
                            $storage_details['suffix'] = $suffix;
                        }
                    } else {
                        $storage_details['type'] = 'external';
                        $storage_details['value'] = $link_to_store;
                    }
                }
            } else {
                $storage_details['value'] = $link_to_store;
                $storage_details['type'] = 'external';
            }
        }

        return json_encode($storage_details);
    }
}