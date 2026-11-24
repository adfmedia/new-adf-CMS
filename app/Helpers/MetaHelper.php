<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

class MetaHelper
{
    /**
     * Local array storing all custom meta settings
     * 
     * @var array
     */
    protected static $META  = [];

    /**
     * Return a processed, ordered set of social meta to include on web pages
     * 
     * @param mixed $UrlObject  The URL object currently being viewed
     * @param mixed $Page       The page currently being viewed
     * @return array
     */
    public static function getSocialMeta(mixed $UrlObject, mixed $Page): array
    {
        return Cache::remember(
            'social_meta_' . ($UrlObject ? 'url_' . $UrlObject->id : 'default'),
            config('cache.timings.page_meta_cache'),
            function () use ($UrlObject, $Page) {
                self::populateBaseMeta($UrlObject, $Page);

                $meta = [];
                foreach (self::$META as $hash => $tag_props) {
                    $meta[$hash] = $tag_props;
                }

                $ordered = self::sortSocialMeta($meta);
                return self::setMetaTypes($ordered);
            }
        );
    }

    /**
     * Return a HTML stringable version of the social meta to include on
     * a web page, allowing direct output without a loop required
     * 
     * @param mixed $UrlObject  The URL ojbect currently being viewed
     * @param mixed $Page       The page currently being viewed
     * @return HtmlString
     */
    public static function outputSocialMeta(mixed $UrlObject, mixed $Page): HtmlString
    {
        $innards = "";
        foreach (self::getSocialMeta($UrlObject, $Page) as $item) {
            $innards .= self::generateTagHtml($item)->toHtml();
        }
        return new HtmlString($innards);
    }

    /**
     * Set a new meta property, with a value
     * 
     * @param string $key
     * @param mixed $content
     */
    public static function setMeta(string $key, mixed $content): void
    {
        self::$META[self::getMetaArrKey($key)] = [
            'property'  => $key,
            'content'   => $content
        ];
    }

    /**
     * Populate the 'base' meta properties required for social sharing
     * 
     * @param mixed $UrlObject
     * @param mixed $Page
     */
    protected static function populateBaseMeta(mixed $UrlObject, mixed $Page): void
    {
        list($og_img, $tw_img) = self::getSocialShareImageAttributes($Page);

        $defaults = [
            // Facebook app ID (required??)
            'fb:app_id'     => '',

            // Opengraph share
            'og:type'           => 'website',
            'og:site_name'      => config('app.name'),
            'og:title'          => ($UrlObject ? $UrlObject->meta_title : ''),
            'og:image'          => $og_img['image'],
            'og:image:type'     => $og_img['type'],
            'og:image:width'    => $og_img['width'],
            'og:image:height'   => $og_img['height'],
            'og:url'            => ($Page ? $Page->url : route('theme')),
            'og:description'    => ($UrlObject ? $UrlObject->meta_description : ''),
            'og:locale'         => 'en_GB',
            'article:author'    => common_text('social_facebook'),

            // Twitter share
            'twitter:card'          => 'summary_large_image',
            'twitter:site'          => '@' . self::getTwitterUsername(),
            'twitter:creator'       => '@' . self::getTwitterUsername(),
            'twitter:domain'        => str_replace(['https://', 'http://'], '', route('theme')),
            'twitter:title'         => ($UrlObject ? $UrlObject->meta_title : ''),
            'twitter:image'         => $tw_img['image'],
            'twitter:image:alt'     => config('app.name'),
            'twitter:url'           => ($Page ? $Page->url : route('theme')),
            'twitter:description'   => ($UrlObject ? $UrlObject->meta_description : ''),
        ];

        foreach ($defaults as $key => $content) {
            if (!isset(self::$META[self::getMetaArrKey($key)])) {
                self::setMeta($key, $content);
            }
        }
    }

    /**
     * Sort the social meta properties into a sensible order
     * 
     * @param array $meta
     * @return array
     */
    protected static function sortSocialMeta(array $meta): array
    {
        uasort($meta, function ($a, $b) {
            $a_prop = $a['property'];
            $b_prop = $b['property'];

            if ($a_prop == 'og:image') {
                if (in_array($b_prop, array('og:image:width', 'og:image:height', 'og:image:type'))) {
                    return -1;
                }
            } elseif ($a_prop == 'og:image:width') {
                if ($b_prop == 'og:image') {
                    return 1;
                }
                if (in_array($b_prop, array('og:image:height', 'og:image:type'))) {
                    return -1;
                }
            } elseif ($a_prop == 'og:image:height') {
                if (in_array($b_prop, array('og:image', 'og:image:width'))) {
                    return 1;
                }
                if ($b_prop == 'og:image:type') {
                    return -1;
                }
            } elseif ($a_prop == 'og:image:type') {
                if (in_array($b_prop, array('og:image', 'og:image:width', 'og:image:height'))) {
                    return 1;
                }
            }

            return strcmp($a['property'], $b['property']);
        });
        return $meta;
    }

    /**
     * Assign a 'type' to the meta properties, used to determine what
     * they output as
     * 
     * @param array $meta
     * @return array
     */
    protected static function setMetaTypes(array $meta): array
    {
        foreach ($meta as $hash => $tag_props) {
            if (
                stristr($tag_props['property'], 'og:') !== false || 
                stristr($tag_props['property'], 'fb:') !== false || 
                stristr($tag_props['property'], 'article:') !== false
            ) {
                $meta[$hash]['type'] = 'property';
            } else {
                $meta[$hash]['type'] = 'name';
            }
        }

        return $meta;
    }

    /**
     * Process the meta item array & convert into an outputtable
     * HtmlString entity
     * 
     * @param array $meta_item
     * @return HtmlString
     */
    protected static function generateTagHtml(array $meta_item): HtmlString
    {
        return new HtmlString("<meta {$meta_item['type']}=\"{$meta_item['property']}\" content=\"{$meta_item['content']}\">");
    }

    /**
     * Return a consistently formatted key for the local array
     * 
     * @param string
     * @return string
     */
    protected static function getMetaArrKey(string $key): string
    {
        return md5($key);
    }

    /**
     * Return an array of image details, used for social sharing
     * 
     * @param mixed $Page
     * @return array
     */
    protected static function getSocialShareImageAttributes(mixed $Page): array
    {
        $attrs = [
            'og'    => [
                'image'     => Vite::asset('resources/images/og-share.jpg'),
                'width'     => '1200',
                'height'    => '630',
                'type'      => 'image/jpeg'
            ],
            'tw'    => [
                'image'     => Vite::asset('resources/images/tw-share.jpg'),
            ]
        ];

        if (is_object($Page) && !empty($Page->image) && !empty($Page->image['social_share'])) {
            $attrs['og']['image'] = $Page->image['social_share']['src'];
            $attrs['og']['width'] = $Page->image['social_share']['width'];
            $attrs['og']['height'] = $Page->image['social_share']['height'];
        }
        if (is_object($Page) && !empty($Page->image) && !empty($Page->image['social_share'])) {
            $attrs['tw']['image'] = $Page->image['social_share']['src'];
        }

        return array_values($attrs);
    }

    /**
     * Return the current twitter username for the site, based
     * on the URL set in the CMS
     * 
     * @return string
     */
    protected static function getTwitterUsername(): string
    {
        $url = common_text('social_twitter');
        if (empty($url)) { return ''; }

        return ltrim(parse_url($url, PHP_URL_PATH), '/');
    }
}