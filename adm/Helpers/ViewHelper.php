<?php

namespace Adm\Helpers;

use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;

class ViewHelper
{
    /**
     * Set the area to load views from
     * 
     * @param string $area
     * @return void
     */
    public static function setViews(string $area = 'adm'): void
    {
        switch ($area) {
            case 'app':
                self::setViewFinder('resources/views', 'app');
                break;
            case 'adm':
            default:
                self::setViewFinder('resources/adm/views', 'adm');
                break;
        }
    }

    /**
     * Override the current view finder to read from the
     * path provided
     * 
     * @param string $path
     * @param string $area
     * @return void
     */
    protected static function setViewFinder(string $path, string $area): void
    {
        /** @var FileViewFinder */
        $currentViewFinder = View::getFinder();
        $themeFinder = new FileViewFinder(app()['files'], array(realpath(base_path($path))));
        View::setFinder($themeFinder);
        foreach ($currentViewFinder->getHints() as $namespace => $paths) {
            if ($area == 'adm' && $namespace == 'pagination') {
                $paths = array_filter($paths, function ($path) {
                    return $path != realpath(base_path('resources/views')) . '/vendor/pagination';
                });
                $adm_pagination_path = realpath(base_path('resources/adm/views')) . '/vendor/pagination';
                if (!in_array($adm_pagination_path, $paths)) {
                    array_unshift($paths, $adm_pagination_path);
                }
            }
            view()->addNamespace($namespace, $paths);
        }
    }
}