<?php

namespace App\Helpers;

use App\Http\Controllers\FileController;
use App\Models\File;

class ImageHelper
{
    /**
     * Dynamically calculates an image's width & height after it's been
     * manipulated to fill or fit the provided dimensions
     * 
     * @param File $File
     * @param int $width
     * @param int $height
     * @param string $style
     * @return array
     */
    public static function getImageUrlWidthHeightForDimensions(File $File, int $width, int $height, string $style = 'fit'): array
    {
        $url = self::getImageUrlForMaxDimensions($File, $width, $height, $style);
        if ($style == 'fill') {
            $img_w = $width;
            $img_h = $height;
        } else {
            list($img_w, $img_h) = self::getImageWidthAndHeightWhenFitted($File, $width, $height);
        }

        return [$url, $img_w, $img_h];
    }

    /**
     * Gets the URL to serve a resized image at the provided size & fill/fit style
     * 
     * @param File $File
     * @param int $width
     * @param int $height
     * @param string $style
     * @return string
     */
    public static function getImageUrlForMaxDimensions(File $File, int $width, int $height, string $style = 'fit'): string
    {
        if (!$File->is_image) {
            return route('file', $File->full_path);
        }

        $route = $style == 'fill' ? 'image.fill' : 'image.fit';
        return route($route, ['dimensions' => $width . 'x' . $height, 'path' => $File->full_path]) . '?' . strtotime($File->updated_at);
    }

    /**
     * Get a [$width, $height] array for the provided file, where $width & $height are
     * the final size the image will be once resized to fit inside the provided $width
     * & $height
     * 
     * @param File $File
     * @param int $width
     * @param int $height
     * @return array
     */
    public static function getImageWidthAndHeightWhenFitted(File $File, int $width, int $height): array
    {
        $fit_width = $fit_height = 0;

        if (self::isNonManipulatedImage($File)) {
            return [$width, $height];
        }

        $width_ratio = self::getWidthToHeightRatio($File->width, $File->height);
        if ($width_ratio < 1) { // Portrait
            $fit_height = $height;
            $fit_width = (int)($width * $width_ratio);
        }
        elseif ($width_ratio > 1) { // Landscape
            $fit_width = $width;
            $fit_height = (int)($height / $width_ratio);
        } else { // Square
            $fit_width = $width;
            $fit_height = $height;
        }

        return [$fit_width, $fit_height];
    }

    /**
     * Get a [$width, $height] array for the provided file, where $width & $height are 
     * the final size the image will be once resized to fill the provided $to_w & $to_h
     * sizes - which may change based on the original image size ($request_w / $request_h) 
     * if it needs cropping
     * 
     * @param File $File
     * @param int $request_w
     * @param int $request_h
     * @param int $to_w
     * @param int $to_h
     * @return array
     */
    public static function getImageWidthAndHeightWhenFilled(File $File, int $request_w, int $request_h, int $to_w, int $to_h): array
    {
        $target_w = $to_w;
        $target_h = $to_h;

        if (self::isNonManipulatedImage($File)) {
            return [$target_w, $target_h];
        }

        if ($request_w > $request_h) {
            $scale = $to_w / $request_w;
            $target_h = round($request_h * $scale);
        } elseif ($request_h > $request_w) {
            $scale = $to_h / $request_h;
            $target_w = round($request_w * $scale);
        } else {
            // Don't need to change sizes as both src & dest are square
        }

        return [$target_w, $target_h];
    }

    /**
     * Get the aspect ratio for the provided width & height
     * 
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function getAspectRatio(int $width, int $height): string
    {
        if ($width == 0 || $height == 0) { return '0'; }
        
        $gcd = self::greatestCommonDenominator($width, $height);
        return ($width / $gcd) . ':' . ($height / $gcd);
    }

    /**
     * Get the width to height ratio for the provided dimensions
     * 
     * @param int $width
     * @param int $height
     * @return float
     */
    public static function getWidthToHeightRatio(int $width, int $height): float
    {
        if ($width == 0 || $height == 0) { return 0; }
        return $width / $height;
    }

    /**
     * Get the greatest common denominator of the 2 provided values
     * 
     * @param int $x
     * @param int $y
     * @return int
     */
    public static function greatestCommonDenominator(int $x, int $y): int
    {
        return ($x % $y) ? self::greatestCommonDenominator($y, ($x % $y)) : $y;
    }

    /**
     * Determine whether the provided file is an image, but cannot be
     * manipulated, based on it's extension
     * 
     * @param File $File
     * @return bool
     */
    public static function isNonManipulatedImage(File $File): bool
    {
        return in_array($File->extension, FileController::$_IMG_EXTS_NO_MANIPULATION);
    }
}