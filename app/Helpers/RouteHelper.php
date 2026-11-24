<?php

namespace App\Helpers;

use App\Models\Url;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;

class RouteHelper
{
    /**
     * Determine whether the current request route is a CMS
     * route or not
     * 
     * @return bool
     */
    public static function isCmsRoute(): bool
    {
        $Route = Route::current();
        if (!$Route || self::isPreviewRoute()) { return false; }

        return in_array($Route->getName(), [
            'adm.route',
            'adm.file-manager',
            'adm.ajax-tool'
        ]);
    }

    /**
     * Determine whether the current request route is a CMS
     * preview route or not
     * 
     * @return bool
     */
    public static function isPreviewRoute(): bool
    {
        $Route = Route::current();
        return $Route && $Route->getName() == 'adm.route.preview';
    }

    /**
     * Generate breadcrumbs for the provided URL path, optionally
     * including the current page
     * 
     * @param string $url_path
     * @param bool $include_current
     * @return array
     */
    public static function generateBreadcrumbs(string $url_path, bool $include_current = true): array
    {
        $crumbs = [];

        $url_path = str_replace(route('theme'), '', $url_path);
        $url_parts = array_filter(explode('/', $url_path));
        if (empty($url_parts)) {
            return $crumbs;
        }

        if (!$include_current) {
            array_pop($url_parts);
        }

        while (!empty($url_parts)) {
            $crumb_url = implode('/', $url_parts);
            $CrumbUrlRecord = Url::where('url', $crumb_url)->first();
            if ($CrumbUrlRecord && $Page = $CrumbUrlRecord->urlable) {
                $crumbs[] = [
                    'url'   => $Page->url,
                    'text'  => $Page->title
                ];
            }
            array_pop($url_parts);
        }

        if (LanguageHelper::getLanguage()->base_url == LanguageHelper::getLanguage()->fqdn) {
            $crumbs[] = [
                'url'   => route('theme'),
                'text'  => 'Home'
            ];
        }

        return array_reverse($crumbs);
    }

    /**
     * Get pagination rel="next", rel="prev" and rel="canonical" links
     * for the provided set of results & page
     * 
     * @param LengthAwarePaginator $PaginatedResults
     * @param Model $Page
     * @param array $query_exclusions
     * @param string $page_qs_param
     * @return array
     */
    public static function getPaginationRelLinks(
        LengthAwarePaginator $PaginatedResults,
        Model $Page,
        array $query_exclusions = [],
        string $page_qs_param   = 'page'
    ): array
    {
        $current_page = request()->input($page_qs_param, 1);
        $canonical = $Page->url;

        $next = null;
        $prev = null;

        if ($PaginatedResults->hasMorePages()) {
            $next = $PaginatedResults->nextPageUrl();
        }

        if ($current_page == 1) {
            $query_exclusions[] = 'page';
            $qs = http_build_query(request()->except($query_exclusions));
            if (!empty($qs)) {
                $canonical .= '?' . $qs;
            }
        }
        else {
            $prev = $PaginatedResults->previousPageUrl();

            $qs = http_build_query(request()->except($query_exclusions));
            if (!empty($qs)) {
                $canonical .= '?' . $qs;
            }
        }

        return array_filter([
            'canonical' => $canonical,
            'next'      => $next,
            'prev'      => $prev
        ]);
    }
}