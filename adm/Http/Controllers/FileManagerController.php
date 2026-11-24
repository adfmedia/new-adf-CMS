<?php

namespace Adm\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\File;
use App\Models\FileFolder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Image as ImageFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileManagerController extends AdmController
{
    /**
     * Load the file manager property
     * 
     * @return \Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        /** @var \Adm\Models\AdmUser */
        $User = Auth::guard('adm')->user();
        if (!$User->canAccess('file-manager')) {
            return response()->redirectTo(route('adm.route'));
        }

        return view(
            'file-manager.index',
            [
                'breadcrumbs'   => [
                    ['url' => route('adm.route'), 'title' => 'Dashboard'],
                    ['url' => route('adm.file-manager'), 'title' => 'File Manager']
                ]
            ]
        );
    }

    /**
     * Returns a JSON response of all folders for the sidebar
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function load_folders(): JsonResponse
    {
        $selected_folder = request()->input('parent');
        $expanded = request()->input('expanded', []);
        if (!empty($expanded) && !is_array($expanded)) { $expanded = explode(',', $expanded); }
        if (is_null($expanded)) { $expanded = []; }

        $TopLevelFolders = FileFolder::where('parent_id', 0)->orderBy('title')->get();

        $folders = [
            [
                'id'        => 0,
                'title'     => 'Site files',
                'children'  => [],
                'expanded'  => false,
                'selected'  => ($selected_folder == 0)
            ]
        ];
        foreach ($TopLevelFolders as $Folder) {
            $this->generateSidebarFolders($Folder, $selected_folder, $expanded, $folders);
        }

        return response()->json(['folders' => $folders]);
    }

    /**
     * Returns a JSON response of all files & folders to display
     * in the main panel
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function load_files(): JsonResponse
    {
        $folder = request()->input('folder');
        $search = request()->input('search');
        
        if ($search) {
            $Folders = FileFolder::matchingSearchTerm($search)->orderBy('title')->get();
        } else {
            $Folders = FileFolder::where('parent_id', $folder)->orderBy('title')->get();
        }
        
        $folders = [];
        foreach ($Folders as $Folder) {
            $folders[] = [
                'id'    => $Folder->id,
                'title' => $Folder->title,
                'path'  => $Folder->path
            ];
        }

        if ($search) {
            $Files = File::matchingSearchTerm($search);
        } else {
            $Files = File::inFolder($folder);
        }

        if (request()->input('image_scope')) {
            $Files->imagesOnly();
        }
        $Files = $Files->orderBy('title')->get();
        $files = [];
        $preview_width = 100; $preview_height = 100;
        foreach ($Files as $File) {
            $width = $height = 0;
            if ($File->is_image) {
                $fit_dims = ImageHelper::getImageWidthAndHeightWhenFitted($File, $preview_width, $preview_height);
                $width = $fit_dims[0];
                $height = $fit_dims[1];
            }
            $files[] = [
                'id'        => $File->id,
                'title'     => $File->title,
                'path'      => $File->file_path,
                'is_image'  => $File->is_image,
                'ext'       => $File->extension,
                'icon_type' => $File->icon_type,
                'image'     => [
                    'src'       => ImageHelper::getImageUrlForMaxDimensions($File, $preview_width, $preview_height),
                    'alt'       => $File->alt_text,
                    'width'     => $width,
                    'height'    => $height
                ]
            ];
        }

        return response()->json(['files' => $files, 'folders' => $folders]);
    }

    /**
     * Returns a JSON response of file details for use in
     * the view file modal
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function load_file_details(): JsonResponse
    {
        $File = File::find(request()->input('file'));
        if (!$File) {
            return response()->json(['success' => false]);
        }

        $preview_width = 550; $preview_height = 550;
        $width = $height = 0;
        if ($File->is_image) {
            $fit_dims = ImageHelper::getImageWidthAndHeightWhenFitted($File, $preview_width, $preview_height);
            $width = $fit_dims[0];
            $height = $fit_dims[1];
        }

        return response()->json([
            'success'       => true,
            'file_id'       => $File->id,
            'title'         => $File->title,
            'alt_text'      => $File->alt_text,
            'file_size'     => $File->filesize,
            'extension'     => $File->extension,
            'uploaded_date' => $File->created_at->format('jS F Y, g:ia'),
            'file_url'      => $File->is_image ? route('image', $File->full_path) : route('file', $File->full_path),
            'is_image'      => $File->is_image,
            'is_editable'   => $File->is_editable,
            'dimensions'    => $File->dimensions,
            'file_preview'  => $File->is_file_previewable,
            'image'         => [
                'src'           => ImageHelper::getImageUrlForMaxDimensions($File, $preview_width, $preview_height),
                'alt'           => $File->alt_text,
                'width'         => $width,
                'height'        => $height
            ]
        ]);
    }

    /**
     * Saves updates to a file's details from the view
     * file modal
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save_file_details(): JsonResponse
    {
        $File = File::find(request()->input('file'));
        if (!$File) {
            return response()->json(['success' => false]);
        }

        $rules = [
            'title' => 'required'
        ];
        if ($File->is_image) {
            $rules['alt_text'] = 'required';
        }

        $this->validate(
            request(),
            $rules,
            [
                'title.required'    => 'Please provide a file name',
                'alt_text.required' => 'Please provide some alt text'
            ]
        );

        $path = Str::slug(request()->input('title')) . '.' . $File->extension;
        if (File::checkDuplicateFilePath($path, $File->file_folder_id, $File->id)) {
            return response()->json([
                'success'   => false,
                'error'     => 'Sorry, that file name is already in use within this folder.'
            ]);
        }

        $File->title = request()->input('title');
        $File->file_path = Str::slug(request()->input('title')) . '.' . $File->extension;
        $File->alt_text = request()->input('alt_text', request()->input('title'));
        $File->save();

        $this->Crud->flushCache();

        return response()->json([
            'success'   => true,
            'file_id'   => $File->id
        ]);
    }

    /**
     * Replaces an existing file with a new one uploaded
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function replace_file(): JsonResponse
    {
        $File = File::find(request()->input('file_id'));
        if (!$File) {
            return response()->json([
                'success'   => false,
                'error'     => 'Unable to find file'
            ]);
        }

        $upload = request()->file('file');
        if (!$upload->isValid()) {
            return response()->json([
                'success'   => false,
                'error'     => $upload->getErrorMessage()
            ]);
        }

        $storage_path = $upload->store('');
        $File->storage_path = $storage_path;
        $File->save();

        if ($File->is_image) {
            $dims = $this->getImageSize($File, $storage_path);
            if (is_array($dims)) {
                $File->width = $dims[0];
                $File->height = $dims[1];
                $File->save();
            }
        }

        $this->Crud->flushCache();

        return response()->json([
            'success'   => true,
            'file_id'   => $File->id
        ]);
    }

    /**
     * Deletes a file
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_file(): JsonResponse
    {
        $File = File::find(request()->input('file'));
        if (!$File) {
            return response()->json([
                'success'   => false,
                'error'     => 'Unable to find file'
            ]);
        }

        $File->delete();

        $this->Crud->flushCache();

        return response()->json([
            'success'   => true
        ]);
    }

    /**
     * Performs manipulation of an image, rotating
     * or flipping/mirroring it
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function manipulate_file(): JsonResponse
    {
        if (!config('image.editable')) {
            return response()->json([
                'success'   => false,
                'error'     => 'Image editing not available'
            ]);
        }

        $File = File::find(request()->input('file'));
        if (!$File) {
            return response()->json([
                'success'   => false,
                'error'     => 'Unable to find file'
            ]);
        }

        $Image = ImageFacade::fromStorage($File->storage_path);
        switch (request()->input('op')) {
            case 'rotate':
                switch (request()->input('dir')) {
                    case 'anticlockwise':
                        $Image = $Image->rotate(-90);
                        break;
                    case 'clockwise':
                        $Image = $Image->rotate(90);
                        break;
                }
                break;
            case 'flip':
                switch (request()->input('dir')) {
                    case 'vertical':
                        $Image = $Image->flipVertically();
                        break;
                    case 'horizontal':
                        $Image = $Image->flipHorizontally();
                        break;
                }
                break;
        }
        $saved = $Image->storeAs(path: '', name: $File->storage_path);

        $File->width = $Image->width();
        $File->height = $Image->height();
        $File->updated_at = date("Y-m-d H:i:s");
        $File->save();

        Cache::flush();

        return response()->json([
            'success'   => true,
            'file_id'   => $File->id,
            'path'      => $File->storage_path,
            'saved'     => $saved
        ]);
    }

    /**
     * Adds a new folder
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function add_folder(): JsonResponse
    {
        $this->validate(
            request(),
            ['title'            => 'required' ],
            ['title.required'   => 'Please provide a folder title / name']
        );

        $Folder = FileFolder::create([
            'parent_id' => request()->input('parent_id', 0),
            'path'      => Str::slug(request()->input('title')),
            'title'     => request()->input('title')
        ]);

        $this->Crud->flushCache();

        return response()->json([
            'success'   => true,
            'id'        => $Folder->id,
            'title'     => $Folder->title
        ]);
    }

    /**
     * Deletes a folder, moving it's child files & folders
     * to it's parent
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_folder(): JsonResponse
    {
        $Folder = FileFolder::find(request()->input('folder'));
        if (!$Folder) {
            return response()->json([
                'success'   => false
            ]);
        }

        // Move child files & folders to parent
        foreach ($Folder->files as $ChildFile) {
            $ChildFile->file_folder_id = $Folder->parent_id;
            $ChildFile->save();
        }
        foreach ($Folder->children as $Child) {
            $Child->parent_id = $Folder->parent_id;
            $Child->save();
        }

        $json_folder = [
            'id'    => $Folder->parent_id,
            'title' => $Folder->parent ? $Folder->parent->title : 'Site files'
        ];
        $Folder->delete();

        $this->Crud->flushCache();

        return response()->json([
            'success'   => true,
            'folder'    => $json_folder
        ]);
    }

    /**
     * Uploads a file or files
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload_files(): JsonResponse
    {
        $errs = [];
        foreach (request()->file('files') as $uploadFile) {
            if (!$uploadFile->isValid()) {
                $errs[] = $uploadFile->getErrorMessage();
            }
        }

        if (!empty($errs)) {
            return response()->json([
                'success'   => false,
                'error'     => implode(', ', $errs)
            ]);
        }

        $target_folder_id = request()->input('folder_id', 0);
        $TargetFolder = FileFolder::find($target_folder_id);
        $store_folder_id = $TargetFolder ? $TargetFolder->id : 0;

        $uploaded = 0;
        $uploaded_file_ids = [];
        foreach (request()->file('files') as $uploadFile) {
            $storage_path = $uploadFile->store('');

            $origName = $uploadFile->getClientOriginalName();
            $upl_path_parts = explode('/', $origName);
            $file_name = end($upl_path_parts);

            $name_parts = explode('.', $file_name);
            $ext = strtolower(array_pop($name_parts));
            $file_name = implode('-', $name_parts);
            $title = str_replace('-', ' ', $file_name);
            $path = Str::slug($file_name) . '.' . $ext;

            if (File::checkDuplicateFilePath($path, $store_folder_id)) {
                $path = Str::slug($file_name) . '-' . time() . '.' . $ext;
            }

            $File = File::create([
                'file_folder_id'    => $store_folder_id,
                'storage_path'      => $storage_path,
                'file_path'         => $path,
                'title'             => $title,
                'alt_text'          => $title,
                'extension'         => $ext
            ]);
            if ($File->is_image) {
                $dims = $this->getImageSize($File, $storage_path);
                if (is_array($dims)) {
                    $File->width = $dims[0];
                    $File->height = $dims[1];
                    $File->save();
                }

                $this->tinyifyImage($storage_path);
            }

            $uploaded++;
            $uploaded_file_ids[] = $File->id;
        }

        $this->Crud->flushCache();

        return response()->json([
            'success'       => true,
            'folder_id'     => $TargetFolder ? $TargetFolder->id : 0,
            'folder_title'  => $TargetFolder ? $TargetFolder->title : 'Site files',
            'uploaded'      => $uploaded,
            'uploaded_ids'  => $uploaded_file_ids
        ]);
    }

    /**
     * Moves a file or folder to another folder
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function move_item(): JsonResponse
    {
        $item_type = request()->input('item_type');
        $item_id = request()->input('item_id');
        $dest_folder = request()->input('dest_folder');

        if (!$item_type || !$item_id) {
            return response()->json([
                'success'   => false
            ]);
        }

        if ($dest_folder) {
            $Dest = FileFolder::find($dest_folder);
            if (!$Dest) {
                return response()->json([
                    'success'   => false
                ]); 
            }
        }

        switch ($item_type) {
            case 'file':
                $File = File::find($item_id);
                if (!$File) {
                    return response()->json([
                        'success'   => false
                    ]);
                }

                $File->file_folder_id = $dest_folder;
                $File->save();
                break;
            case 'folder':
                $Folder = FileFolder::find($item_id);
                if (!$Folder) {
                    return response()->json([
                        'success'   => false
                    ]);
                }

                $Folder->parent_id = $dest_folder;
                $Folder->save();
                break;
            default:
                return response()->json([
                    'success'   => false
                ]);
        }

        $this->Crud->flushCache();

        return response()->json([
            'success'   => true
        ]);
    }

    /**
     * Common function loader for use with AJAX/fetch
     * calls
     * 
     * @param string $requested_function
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function fn_loader(string $requested_function): mixed
    {
        if (method_exists($this, $requested_function)) {
            return $this->$requested_function();
        }

        throw new NotFoundHttpException();
    }

    /**
     * Generates a nested folder array for the sidebar
     * menu
     * 
     * @param \App\Models\FileFolder $Folder
     * @param ?int $selected_folder
     * @param array $expanded
     * @param array $folders
     * @return void
     */
    protected function generateSidebarFolders(FileFolder $Folder, ?int $selected_folder, array $expanded, array &$folders): void
    {
        $selected = $selected_folder == $Folder->id;
        $children = [];
        foreach ($Folder->children as $ChildFolder) {
            $this->generateSidebarFolders($ChildFolder, $selected_folder, $expanded, $children);
        }

        $folders[] = [
            'id'        => $Folder->id,
            'title'     => $Folder->title,
            'children'  => $children,
            'expanded'  => in_array($Folder->id, $expanded) || $selected,
            'selected'  => $selected
        ];
    }

    /**
     * Attempts to get the width & height of an uploaded image
     * 
     * @param \App\Models\File $File
     * @param string $storage_path
     * @return ?array
     */
    protected function getImageSize(File $File, string $storage_path): ?array
    {
        try {
            if ($File->extension == 'svg') {
                $svg_contents = file_get_contents(storage_path('app/' . $storage_path));
                preg_match('/<svg.*width="?([\d\.]*)"?.*/i', $svg_contents, $width_match);
                preg_match('/<svg.*height="?([\d\.]*)"?.*/i', $svg_contents, $height_match);
                preg_match('/<svg.*viewbox="?([\d\.\s]*)"?.*/i', $svg_contents, $viewbox_match);

                if (!empty($width_match) && isset($width_match[1])) { $width = $width_match[1]; }
                if (!empty($height_match) && isset($height_match[1])) { $height = $height_match[1]; }

                if (empty($width) && empty($height) && !empty($viewbox_match) && isset($viewbox_match[1])) {
                    $viewbox = $viewbox_match[1];
                    list($ox, $oy, $ex, $ey) = explode(' ', $viewbox);
                    $width = $ex - $ox;
                    $height = $ey - $oy;
                }
            } else {
                $dims = getimagesize(storage_path('app/' . $storage_path));
                if (isset($dims[0])) { $width = $dims[0]; }
                if (isset($dims[1])) { $height = $dims[1]; }
            }

            return [$width, $height];
        } catch (Exception $e) {}

        return null;
    }

    /**
     * Optimise the provided image file using the Tinify
     * API (when enabled)
     * 
     * @param string $storage_path
     * @return void
     */
    protected function tinyifyImage(string $storage_path): void
    {
        if (!config('services.tinify.enabled') || !config('services.tinify.api_key')) {
            return;
        }

        try {
            $full_path = Storage::path($storage_path);
            \Tinify\setKey(config('services.tinify.api_key'));
            $Source = \Tinify\fromFile($full_path);
            $Source->toFile($full_path);
        } catch (Exception $e) {}
    }
}