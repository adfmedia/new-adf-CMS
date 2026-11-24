<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Language
    |--------------------------------------------------------------------------
    |
    | This value is the default language of the website.
    |
    */

    'default_language_id' => env('APP_DEFAULT_LANGUAGE', 1),

    /*
    |--------------------------------------------------------------------------
    | Form Logging
    |--------------------------------------------------------------------------
    |
    | Settings related to internal form log storage.
    |
    */

    'form_log_retention_period' => env('FORM_LOG_RETENTION_PERIOD', "12 months"),

    /*
    |--------------------------------------------------------------------------
    | Cookie Banner
    |--------------------------------------------------------------------------
    |
    | Settings related the cookie banner.
    |
    */

    'cookie_banner' => [
        'enabled'               => env('COOKIE_BANNER_ENABLED', false),
        'cookie_name_closed'    => env('COOKIE_BANNER_COOKIE_NAME_CLOSED', '_cookie_banner_closed'),
        'cookie_name_accepted'  => env('COOKIE_BANNER_COOKIE_NAME_ACCEPTED', '_cookie_banner_accepted'),
        'cookie_name_rejected'  => env('COOKIE_BANNER_COOKIE_NAME_REJECTED', '_cookie_banner_rejected'),
        'cookie_name_status'    => env('COOKIE_BANNER_COOKIE_NAME_STATUS', '_cookie_banner_status'),
        'include_reject'        => env('COOKIE_BANNER_INCLUDE_REJECT', false),
        'accept_by_default'     => env('COOKIE_BANNER_ACCEPT_BY_DEFAULT', true)
    ],

    /*
    |--------------------------------------------------------------------------
    | CMS Exports
    |--------------------------------------------------------------------------
    |
    | Settings related to CMS exports.
    |
    */

    'cms_exports'   => [
        'default_type'      => env('CMS_EXPORT_DEFAULT_TYPE', 'csv')
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Titles
    |--------------------------------------------------------------------------
    |
    | Settings related to Meta titles.
    |
    */

    'meta_titles'   => [
        'include_site_name' => env('SITE_NAME_IN_META', true)
    ],

    /*
    |--------------------------------------------------------------------------
    | AlpineJS Enabled on Front-end
    |--------------------------------------------------------------------------
    |
    | Setting toggling AlpinsJS on/off for the front-end.
    |
    */

    'alpinejs_fe_enabled'   => env('ALPINEJS_FE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Content replacement domains
    |--------------------------------------------------------------------------
    |
    | Setting additional domains to search for & replace in
    | WYSIWYG content.
    |
    */

    'content_replacement'   => [
        'domains'   => [
            env('CONTENT_REPLACE_DOMAIN_LOCAL', null),
            env('CONTENT_REPLACE_DOMAIN_DEMO', null),
            env('CONTENT_REPLACE_DOMAIN_STAGING', null),
            env('CONTENT_REPLACE_DOMAIN_LIVE', null),
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'Europe/London'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
