<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileFolder;
use Exception;
use Illuminate\Image\Image;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Image as ImageFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
    /**
     * Any image extensions that can't be resized or cropped
     * 
     * @var array
     */
    public static $_IMG_EXTS_NO_MANIPULATION = ['gif', 'svg'];

    /**
     * Any image extensions that can't be converted to webp
     * 
     * @var array
     */
    protected static $_NO_WEBP_IMG_EXTS = ['gif', 'svg'];

    /**
     * Serve a file as is, based on it's path
     * 
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function file(string $path): BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);

        if ($File->extension == 'svg') {
            return response()->file(
                Storage::path($File->storage_path),
                [
                    'content-type'  => 'image/svg+xml',
                    'cache-control' => 'max-age=31536000, public'
                ]
            );
        }

        if(request()->has('download')) {
            $original_name = implode('.', [$File->title, $File->extension]);
            return response()->download(Storage::path($File->storage_path), $original_name);
        } 

        return response()->file(Storage::path($File->storage_path));
    }

    /**
     * Serve an image as is, based on it's path
     * 
     * @param string $path
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function image_raw($path): Response|BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);
        if ($this->shouldServeImageThroughFile($File)) {
            return $this->file($File->full_path);
        }

        return $this->resizeCropServeImage($File, null, null, false, 'fit');
    }

    /**
     * Serve an image cropped to fit an area, based on it's path, dimensions
     * and crop position
     * 
     * @param string $dimensions
     * @param string $crop
     * @param string $path
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function image_fit_crop(string $dimensions, string $crop, string $path): Response|BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);
        if ($this->shouldServeImageThroughFile($File)) {
            return $this->file($File->full_path);
        }

        return $this->resizeCropServeImage($File, $dimensions, $crop, true, 'fit');
    }

    /**
     * Serve an image fit inside an area, based on it's path and dimensions
     * 
     * @param string $dimensions
     * @param string $path
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function image_fit($dimensions, $path): Response|BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);
        if ($this->shouldServeImageThroughFile($File)) {
            return $this->file($File->full_path);
        }

        return $this->resizeCropServeImage($File, $dimensions, null, true, 'fit');
    }

    /**
     * Serve an image cropped to fit inside a fixed size area, based on 
     * it's path, dimensions and crop position
     * 
     * @param string $dimensions
     * @param string $crop
     * @param string $path
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function image_fit_in_crop(string $dimensions, string $crop, string $path): Response|BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);
        if ($this->shouldServeImageThroughFile($File)) {
            return $this->file($File->full_path);
        }

        return $this->resizeCropServeImage($File, $dimensions, $crop, true, 'fit-in');
    }

    /**
     * Serve an image fit inside a fixed size area, based on it's path and dimensions
     * 
     * @param string $dimensions
     * @param string $path
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function image_fit_in($dimensions, $path): Response|BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);
        if ($this->shouldServeImageThroughFile($File)) {
            return $this->file($File->full_path);
        }

        return $this->resizeCropServeImage($File, $dimensions, null, true, 'fit-in');
    }

    /**
     * Serve an image cropped to fill an area, based on it's path, dimensions
     * and crop position
     * 
     * @param string $dimensions
     * @param string $crop
     * @param string $path
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function image_fill_crop($dimensions, $crop, $path): Response|BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);
        if ($this->shouldServeImageThroughFile($File)) {
            return $this->file($File->full_path);
        }

        return $this->resizeCropServeImage($File, $dimensions, $crop, true, 'fill');
    }

    /**
     * Serve an image to fill an area, based on it's path and dimensions
     * 
     * @param string $dimensions
     * @param string $path
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function image_fill($dimensions, $path): Response|BinaryFileResponse
    {
        $File = $this->getFileFromPath($path);
        if ($this->shouldServeImageThroughFile($File)) {
            return $this->file($File->full_path);
        }

        return $this->resizeCropServeImage($File, $dimensions, null, true, 'fill');
    }

    /**
     * Returns a File instance matching the provided path,
     * or throws a 404 exception if not found.
     * 
     * @param string $path
     * @return \App\Models\File
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getFileFromPath(string $path): File
    {
        $path_parts = explode('/', $path);
        $file_path = array_pop($path_parts);

        $cache_key = 'f_from_p_' . str_replace(['/'], '_', $path);
        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }

        $ParentFolder = FileFolder::getFolderFromPathParts($path_parts);
        $File = File::where([
            'file_path'         => $file_path,
            'file_folder_id'    => $ParentFolder ? $ParentFolder->id : 0
        ])->first();

        if (!$File || $File->full_path != $path) {
            throw new NotFoundHttpException();
        }

        Cache::put($cache_key, $File, now()->addMinutes((int)config('cache.timings.file_retrieval')));
        return $File;
    }

    /**
     * Determine whether to serve an image through the file method,
     * based on it's extension
     * 
     * @param File $File
     * @return bool
     */
    protected function shouldServeImageThroughFile(File $File): bool
    {
        return in_array($File->extension, self::$_IMG_EXTS_NO_MANIPULATION);
    }

    /**
     * Resizes, crops & serves the image response based on the
     * provided file, path, dimensions, crop & fit/fill style.
     * Optionally caches the images.
     * 
     * @param \App\Models\File $File
     * @param mixed $dimensions
     * @param mixed $crop
     * @param bool $should_cache
     * @param string $style
     * @return \Illuminate\Http\Response
     */
    protected function resizeCropServeImage(
        File $File, 
        mixed $dimensions = null, 
        mixed $crop = null, 
        bool $should_cache = true, 
        string $style = 'fit'
    ): Response
    {
        $CachedImage = $this->getImageFromCache($File, $dimensions, $crop, $style);
        if ($CachedImage) {
            return $this->serveImage($CachedImage, $File, $dimensions, $crop, false, $style);
        }

        $Image = ImageFacade::fromStorage($File->storage_path);
        if (!is_null($crop)) {
            $Image = $this->cropImage($Image, $crop);
        }
        if (!is_null($dimensions)) {
            $Image = $this->resizeImage($Image, $dimensions, $style);
        }

        return $this->serveImage($Image, $File, $dimensions, $crop, $should_cache, $style);
    }

    /**
     * Resizes the provided image to the provided dimensions,
     * based on it's fit/fill style
     * 
     * @param \Illuminate\Image\Image $Image
     * @param string $dimensions
     * @param string $style
     * @return \Illuminate\Image\Image
     */
    protected function resizeImage(Image $Image, string $dimensions, string $style = 'fit'): Image
    {
        $dims = explode('x', $dimensions);
        $width = !empty($dims[0]) ? (int)$dims[0] : null;
        $height = !empty($dims[1]) ? (int)$dims[1] : null;

        switch ($style) {
            case 'fill':
                return $this->fillImage($Image, $width, $height);
            case 'fit-in':
                return $this->fitImageInside($Image, $width, $height);
            case 'fit':
            default:
                return $this->fitImage($Image, $width, $height);
        }
    }

    /**
     * Crops the provided image to the provided size and area
     * 
     * @param \Illuminate\Image\Image $Image
     * @param string $crop
     * @return \Illuminate\Image\Image
     */
    protected function cropImage(Image $Image, string $crop): Image
    {
        $crop_config = explode('-', $crop);

        $crop_size = isset($crop_config[0]) ? explode('x', $crop_config[0]) : null;
        $crop_pos = isset($crop_config[1]) ? explode('x', $crop_config[1]) : null;

        $c_w = $crop_size && isset($crop_size[0]) ? $crop_size[0] : null;
        $c_h = $crop_size && isset($crop_size[1]) ? $crop_size[1] : null;
        $c_x = $crop_pos && sizeof($crop_pos) == 2 ? $crop_pos[0] : null;
        $c_y = $crop_pos && sizeof($crop_pos) == 2 ? $crop_pos[1] : null;

        if (!is_null($c_w) && !is_null($c_h)) {
            $Image = $Image->crop($c_w, $c_h, $c_x, $c_y);
        }

        return $Image;
    }

    /**
     * Fits the provided image into the provided width x height in px
     * 
     * @param \Illuminate\Image\Image $Image
     * @param ?int $width
     * @param ?int $height
     * @return \Illuminate\Image\Image
     */
    protected function fitImage(Image $Image, ?int $width, ?int $height): Image
    {
        return $Image->contain($width, $height, '#ffffff00');
    }

    /**
     * Fits the provided image into the fixed size width x height
     * image canvas
     * 
     * @param \Illuminate\Image\Image $Image
     * @param ?int $width
     * @param ?int $height
     * @return \Illuminate\Image\Image
     */
    protected function fitImageInside(Image $Image, ?int $width, ?int $height): Image
    {
        return $Image->contain($width, $height, '#ffffff00');
    }

    /**
     * Fills the provided width x height in px with the provided image
     * 
     * @param \Illuminate\Image\Image $Image
     * @param ?int $width
     * @param ?int $height
     * @return \Illuminate\Image\Image
     */
    protected function fillImage(Image $Image, ?int $width, ?int $height): Image
    {
        if (is_null($height)) {
            return $Image->scale(width: $width);
        }
        elseif (is_null($width)) {
            return $Image->scale(height: $height);
        }
        else {
            return $Image->cover($width, $height);
        }
    }

    /**
     * Serve an image response
     * 
     * @param \Illuminate\Image\Image $Image
     * @param File $File
     * @param mixed $dimensions
     * @param mixed $crop
     * @param bool $should_cache
     * @param string $style
     * @return \Illuminate\Http\Response
     */
    protected function serveImage(
        Image $Image, 
        File $File, 
        mixed $dimensions = null, 
        mixed $crop = null, 
        bool $should_cache = true,
        string $style = 'fit'
    ): Response
    {
        if ($should_cache) {
            $this->storeImageInCache($Image, $File->storage_path, $dimensions, $crop, $style);
        }

        $EncodedImage = $this->canServeExtensionAsWebp($File->extension) ? $Image->toWebp()->quality(90) : $Image->quality(90);

        $ImgResponse = response($EncodedImage->toBytes());
        $ImgResponse->setPublic();
        $ImgResponse->header('cache-control', 'max-age=31536000, public');
        $ImgResponse->header('content-type', $EncodedImage->mimeType());

        return $ImgResponse;
    }

    /**
     * Determine whether the current image can be served as a webp image
     * or not, based on it's file extension & request header.
     * 
     * @param mixed $extension
     * @return bool
     */
    protected function canServeExtensionAsWebp(mixed $extension): bool
    {
        if (in_array($extension, self::$_NO_WEBP_IMG_EXTS)) {
            return false;
        }
        if (!stristr(request()->header('accept'), 'image/webp')) {
            return false;
        }

        return empty(config('cache.cloudflare.api_key'));
    }

    /**
     * Returns an Image instance if the provided file, path, dimensions, 
     * crop & fit/fill style are found in the cache, or null if not found or expired.
     * 
     * @param \App\Models\File $File
     * @param mixed $dimensions
     * @param mixed $crop
     * @param string $style
     * @return ?\Illuminate\Image\Image
     */
    protected function getImageFromCache(
        File $File, 
        mixed $dimensions = null, 
        mixed $crop = null, 
        string $style = 'fit'
    ): ?Image
    {
        $cache_path = $this->getImageCachePath($File->storage_path, $dimensions, $crop, $style);
        if (!Storage::exists($cache_path)) {
            return null;
        }

        if (Storage::lastModified($cache_path) <= strtotime($File->updated_at)) {
            return null;
        }

        return ImageFacade::fromStorage($cache_path);
    }

    /**
     * Stores the provided image in the cache, based on it's path, 
     * dimensions, crop position & fit/fill style.
     * 
     * @param \Illuminate\Image\Image $Image
     * @param string $file_path
     * @param mixed $dimensions
     * @param mixed $crop
     * @param string $style
     * @return void
     */
    protected function storeImageInCache(
        Image $Image, 
        string $file_path, 
        mixed $dimensions = null, 
        mixed $crop = null,
        string $style = 'fit'
    ): void
    {
        $cache_path = $this->getImageCachePath($file_path, $dimensions, $crop, $style);
        $Image->storeAs(path: '', name: $cache_path);
    }

    /**
     * Returns the local cache storage path for an image, based on
     * the provided path, dimensions, crop & fit/fill style.
     * 
     * @param string $file_path
     * @param mixed $dimensions
     * @param mixed $crop
     * @param string $style
     * @return string
     */
    protected function getImageCachePath(
        string $file_path, 
        mixed $dimensions = null, 
        mixed $crop = null, 
        string $style = 'fit'
    ): string
    {
        $cache_parts = ['cache', $style];
        if (!is_null($dimensions)) {
            $cache_parts[] = $dimensions;
        }
        if (!is_null($crop)) {
            $cache_parts[] = $crop;
        }
        $cache_dir = implode('/', $cache_parts);
        Storage::makeDirectory($cache_dir);

        $cache_parts[] = $file_path;

        return implode('/', $cache_parts);
    }

    public function favicon_manifest(): Response
    {
        $icon_192 = '/images/favicons/android-chrome-192x192.png';
        $icon_512 = '/images/favicons/android-chrome-512x512.png';

        try {
            $icon_192 = Vite::asset('resources' . $icon_192);
            $icon_512 = Vite::asset('resources' . $icon_512);
        } catch (Exception $e) {}

        $manifest = [
            'name'          => config('app.name'),
            'short_name'    => config('app.name'),
            'icons'         => [
                [
                    'src'       => $icon_192,
                    'sizes'     => '192x192',
                    'type'      => 'image/png'
                ],
                [
                    'src'       => $icon_512,
                    'sizes'     => '512x512',
                    'type'      => 'image/png'
                ]
            ],
            'theme_color'       => '#ffffff',
            'background_color'  => '#ffffff',
            'display'           => 'standalone'
        ];

        return response(json_encode($manifest, JSON_PRETTY_PRINT))
            ->header('Content-type', 'document');
    }
}