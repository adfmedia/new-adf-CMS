<?php

namespace App\Helpers;

use Adm\Crud\CrudInterface;
use App\Models\Language;
use Exception;
use Illuminate\Support\Facades\Schema;

class LanguageHelper
{
    /**
     * Static variable storing the current language loaded
     * 
     * @var Language
     */
    protected static $CurrentLang;

    /**
     * Gets the currently loaded language, also setting it
     * if required
     * 
     * @return Language
     */
    public static function getLanguage(): Language
    {
        if (self::$CurrentLang) {
            return self::$CurrentLang;
        }

        self::$CurrentLang = self::determineLanguage();
        return self::$CurrentLang;
    }

    /**
     * Get the language for the current request URL
     * 
     * @return Language
     */
    protected static function determineLanguage(): Language
    {
        if (RouteHelper::isCmsRoute()) {
            $CrudInterface = new CrudInterface();
            return $CrudInterface->getLanguage();
        }

        if (!Schema::hasTable('languages')) {
            return new Language([
                'id'        => 1,
                'title'     => 'English',
                'locale'    => 'en',
                'base_url'  => config('app.url')
            ]);
        }

        $current_url = request()->url();
        $domain = parse_url($current_url, PHP_URL_SCHEME) . '://' . parse_url($current_url, PHP_URL_HOST);
        // Match other languages based on domain/subdomain
        foreach (Language::urlMatchOrder()->get() as $Lang) {
            if ($domain == $Lang->base_url) {
                return $Lang;
            }
        }

        return Language::find(config('app.default_language_id'));
    }

    /**
     * Override the language determined from the current request
     * URL with the provided language ID
     * 
     * @param int $language_id
     * @return void
     */
    public static function overrideLanguage(int $language_id): void
    {
        $Lang = Language::find($language_id);
        if (!$Lang) {
            throw new Exception('Language not found to override with.');
        }

        self::$CurrentLang = $Lang;
    }

    /**
     * Change the current language for the provided one
     * 
     * @param \App\Models\Language $Language
     * @return void
     */
    public static function changeLanguage(Language $Language): void
    {
        self::$CurrentLang = $Language;
    }

    /**
     * Replace the fixed portion of the language base URL
     * from the provided URL slug
     * 
     * @param string $url_slug
     * @return string
     */
    public static function replaceBaseSlug(string $url_slug): string
    {
        $slug = self::getLanguage()->url_slug;
        if (strpos($url_slug, $slug) === 0) {
            $url_slug = $url_slug == $slug ? '/' : substr($url_slug, strlen($slug));
        }
        return $url_slug;
    }
}