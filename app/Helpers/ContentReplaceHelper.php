<?php

namespace App\Helpers;

use App\Models\File;
use App\Models\Language;
use App\Models\Url;

class ContentReplaceHelper
{
    /**
     * Constant format for stored images within website content
     * 
     * @var string
     */
    const IMG_SRC_FORMAT    = '{{image=_ID_}}';

    /**
     * Constant format for stored links within website content
     * 
     * @var string
     */
    const LINK_HREF_FORMAT  = '{{url=_ID_}}';

    /**
     * Local storage variable holding all language based domains
     * registered in the database.
     * Set to null by default to flag that it needs populating.
     * 
     * @var null|array
     */
    protected static $site_domains  = null;

    /**
     * Local storage variable holding all language based domains
     * registered in the database, but indexed by language ID
     * 
     * @var array
     */
    protected static $lang_domains  = [];

    /**
     * Replace any template tags in the provided content, ready for storage in 
     * the database
     * 
     * @param string $content
     * @return string
     */
    public static function replaceContentForStorage(string $content):string
    {
        $content = self::switchImageUrlsForStorage($content);
        $content = self::switchLinkUrlsForStorage($content);
        return $content;
    }

    /**
     * Replace any template tags in the provided content, ready for output on
     * the front-end
     * 
     * @param string $content
     * @return string
     */
    public static function replaceContentFromStorage(string $content): string
    {
        $content = self::switchImageUrlsFromStorage($content);
        $content = self::switchLinkUrlsFromStorage($content);
        return $content;
    }

    /**
     * Switches any found image URLs in the provided content for their
     * template tag counterparts
     * 
     * @param string $content
     * @return string
     */
    protected static function switchImageUrlsForStorage(string $content): string
    {
        preg_match_all(
            '/src="([^"]+)"/',
            $content,
            $image_sources
        );
        if (!empty($image_sources[0]) && !empty($image_sources[1])) {
            foreach ($image_sources[0] as $match_idx => $source_match) {
                if (!isset($image_sources[1][$match_idx])) { continue; }

                $source_url = $image_sources[1][$match_idx];
                if (!self::isWebsiteUrl($source_url)) { continue; }

                $file_id = self::getFileIdFromUrl($source_url);
                if (is_int($file_id)) {
                    $replacement_code = str_replace('_ID_', $file_id, self::IMG_SRC_FORMAT);
                    $content = str_replace(
                        $source_match,
                        'src="' . $replacement_code . '"',
                        $content
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Switches any found image template tags in the provided content for 
     * their URL counterparts
     * 
     * @param string $content
     * @return string
     */
    protected static function switchImageUrlsFromStorage(string $content): string
    {
        $code_parts = explode('_ID_', self::IMG_SRC_FORMAT);
        preg_match_all(
            '/src="' . $code_parts[0] . '([^"]+)' . $code_parts[1] . '"/',
            $content,
            $image_sources
        );
        if (!empty($image_sources[0]) && !empty($image_sources[1])) {
            foreach ($image_sources[0] as $match_idx => $source_match) {
                if (!isset($image_sources[1][$match_idx])) { continue; }

                $matched_id = $image_sources[1][$match_idx];
                $File = File::find($matched_id);
                if (!$File) { continue; }
                
                // And then replace without (for front-end)
                $content = str_replace(
                    $source_match,
                    'src="' . route('image', $File->full_path) . '"',
                    $content
                );
            }
        }

        return $content;
    }

    /**
     * Switches any found link URLs in the provided content for their
     * template tag counterparts
     * 
     * @param string $content
     * @return string
     */
    protected static function switchLinkUrlsForStorage(string $content): string
    {
        preg_match_all(
            '/<a href="([^"]+)"/',
            $content,
            $link_hrefs
        );
        if (!empty($link_hrefs[0]) && !empty($link_hrefs[1])) {
            foreach ($link_hrefs[0] as $match_idx => $href_match) {
                if (!isset($link_hrefs[1][$match_idx])) { continue; }

                $href_url = $link_hrefs[1][$match_idx];
                if (!self::isWebsiteUrl($href_url)) { continue; }

                $url_id = self::getUrlIdFromUrl($href_url);
                if (is_int($url_id)) {
                    $replacement_code = str_replace('_ID_', $url_id, self::LINK_HREF_FORMAT);
                    $content = str_replace(
                        $href_match,
                        '<a href="' . $replacement_code . '"',
                        $content
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Switches any found link template tags in the provided content for
     * their URL counterparts
     * 
     * @param string $content
     * @return string
     */
    protected static function switchLinkUrlsFromStorage(string $content): string
    {
        $code_parts = explode('_ID_', self::LINK_HREF_FORMAT);
        preg_match_all(
            '/<a href="' . $code_parts[0] . '([^"]+)' . $code_parts[1] . '"/',
            $content,
            $link_hrefs
        );
        if (!empty($link_hrefs[0]) && !empty($link_hrefs[1])) {
            foreach ($link_hrefs[0] as $match_idx => $source_match) {
                if (!isset($link_hrefs[1][$match_idx])) { continue; }

                $matched_id = $link_hrefs[1][$match_idx];
                $Url = Url::find($matched_id);
                if (!$Url) { continue; }

                $content = str_replace(
                    $source_match,
                    '<a href="' . (self::getDomainForLanguage($Url->language_id) . '/' . ltrim($Url->url, '/')) . '"',
                    $content
                );
            }
        }

        return $content;
    }

    /**
     * Gets a file's internal storage ID based on it's URL
     * 
     * @param string $url
     * @return int|string
     */
    protected static function getFileIdFromUrl(string $url): int|string
    {
        $relative_url = self::getRelativeWebsiteUrl($url);
        if (stripos($relative_url, 'images/') === 0) {
            $relative_url = substr($relative_url, 7);
        }
        elseif (stripos($relative_url, 'files/') === 0) {
            $relative_url = substr($relative_url, 6);
        }

        $path_parts = explode('/', $relative_url);
        $file_path = array_pop($path_parts);

        $MatchedFiles = File::where('file_path', $file_path)->get();
        foreach ($MatchedFiles as $File) {
            if ($File->full_path == $relative_url) {
                return $File->id;
            }
        }

        return $url;
    }

    /**
     * Gets a link's internal storage ID based on it's URL
     * 
     * @param string $url
     * @return int|string
     */
    protected static function getUrlIdFromUrl(string $url): int|string
    {
        $relative_url = self::getRelativeWebsiteUrl($url);
        $Url = Url::where('url', $relative_url)->first();
        return $Url ? $Url->id : $url;
    }

    /**
     * Determine whether the provided URL is an internal, website URL
     * or not
     * 
     * @param string $url
     * @return bool
     */
    protected static function isWebsiteUrl(string $url): bool
    {
        foreach (self::getSiteDomains() as $site_domain) {
            if (stripos($url, $site_domain) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a relative version of the provided URL if it's found to
     * be an internal link
     * 
     * @param string $url
     * @return string
     */
    protected static function getRelativeWebsiteUrl(string $url): string
    {
        foreach (self::getSiteDomains() as $domain) {
            $url = str_replace($domain, '', $url);
        }
        return ltrim($url, '/');
    }

    /**
     * Loads database stored language domains into the local
     * storage variable if required
     * 
     * @return array
     */
    protected static function getSiteDomains(): array
    {
        if (is_null(self::$site_domains)) {
            $domains = [route('theme')];
            foreach (Language::get()->pluck('base_url')->toArray() as $lang_domain) {
                if (!in_array($lang_domain, $domains)) {
                    $domains[] = $lang_domain;
                }
            }
            foreach (array_filter(config('app.content_replacement.domains')) as $conf_domain) {
                if (!in_array($conf_domain, $domains) && substr(strtolower($conf_domain), 0, 4) == 'http') {
                    $domains[] = $conf_domain;
                }
            }
            self::$site_domains = $domains;
        }

        return self::$site_domains;
    }

    /**
     * Gets the domain for the provided language ID
     * 
     * @param int $language_id
     * @return string
     */
    protected static function getDomainForLanguage(int $language_id): string
    {
        if (!isset(self::$lang_domains[$language_id])) {
            $Lang = Language::find($language_id);
            if ($Lang) {
                self::$lang_domains[$language_id] = $Lang->base_url;
                return $Lang->base_url;
            }
        } else {
            return self::$lang_domains[$language_id];
        }

        return route('theme');
    }
}