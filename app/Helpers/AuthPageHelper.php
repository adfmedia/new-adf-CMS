<?php

namespace App\Helpers;

use App\Models\Url;
use Modules\Pages\Models\Page;

class AuthPageHelper
{
    /**
     * Returns a $Page and $UrlObject variable for
     * use on auth pages (e.g. login, register) when
     * a system page may or may not be configured for
     * consistency
     * 
     * @param string $controller        The controller name for system page lookup
     * @param string $method            The method name for system page lookup
     * @param string $url               The default page URL when no system page is found
     * @param string $meta_title        The default meta title when no system page is found
     * @param string $meta_description  The default meta description when no system page is found
     * @param string $h1_title          The page H1 title when no system page is found
     * @return array
     */
    public static function getPageAndUrlObject(
        string $controller,
        string $method,
        string $url,
        string $meta_title,
        string $meta_description,
        string $h1_title
    ): array
    {
        $Page = Url::getSystemPage($controller, $method);
        $UrlObject = $Page ? $Page->url_record : new Url();
        
        if (!$Page) {
            $Page = new Page([
                'h1_title'  => $h1_title
            ]);
        }
        if (!$UrlObject->url) {
            $UrlObject->url = $url;
            $UrlObject->meta_title = $meta_title;
            $UrlObject->meta_description = $meta_description;
            $UrlObject->no_index = true;
        }

        return [$Page, $UrlObject];
    }
}