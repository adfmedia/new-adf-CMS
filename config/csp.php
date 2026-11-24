<?php

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;

return [

    /*
     * Presets will determine which CSP headers will be set. A valid CSP preset is
     * any class that implements `Spatie\Csp\Preset`
     */
    'presets' => [
        App\Policies\CspPresets\Base::class,

        // App libraries
        App\Policies\CspPresets\AlpineJS::class,
        App\Policies\CspPresets\GLightbox::class,

        // Third party vendors
        Spatie\Csp\Presets\AdobeFonts::class,
        Spatie\Csp\Presets\Clarity::class,
        App\Policies\CspPresets\GoogleAds::class,
        Spatie\Csp\Presets\GoogleAnalytics::class,
        Spatie\Csp\Presets\GoogleFonts::class,
        Spatie\Csp\Presets\GoogleMaps::class,
        Spatie\Csp\Presets\GoogleRecaptcha::class,
        Spatie\Csp\Presets\GoogleTagManager::class,
        Spatie\Csp\Presets\GoogleTlds::class,
        Spatie\Csp\Presets\HotJar::class,
        Spatie\Csp\Presets\HubSpot::class,
        App\Policies\CspPresets\LinkedIn::class,
        Spatie\Csp\Presets\MetaPixel::class,
        App\Policies\CspPresets\TwitterX::class,
        Spatie\Csp\Presets\Vimeo::class,
        App\Policies\CspPresets\YouTube::class,
    ],

    /**
     * Register additional global CSP directives here.
     */
    'directives' => [
        // [Directive::SCRIPT, [Keyword::UNSAFE_EVAL, Keyword::UNSAFE_INLINE]],
        [Directive::STYLE, [Keyword::UNSAFE_INLINE]],
        [Directive::IMG, ['*', 'data:']],
        [Directive::FONT, ['data:']],
    ],

    /*
     * These presets which will be put in a report-only policy. This is great for testing out
     * a new policy or changes to existing CSP policy without breaking anything.
     */
    'report_only_presets' => [
        //
    ],

    /**
     * Register additional global report-only CSP directives here.
     */
    'report_only_directives' => [
        // [Directive::SCRIPT, [Keyword::UNSAFE_EVAL, Keyword::UNSAFE_INLINE]],
    ],

    /*
     * All violations against the policy will be reported to this url.
     * A great service you could use for this is https://report-uri.com/
     *
     * You can override this setting by calling `reportTo` on your policy.
     */
    'report_uri' => env('CSP_REPORT_URI', ''),

    /*
     * Headers will only be added if this setting is set to true.
     */
    'enabled' => env('CSP_ENABLED', true),

    /**
     * Headers will be added when Vite is hot reloading.
     */
    'enabled_while_hot_reloading' => env('CSP_ENABLED_WHILE_HOT_RELOADING', false),

    /*
     * The class responsible for generating the nonces used in inline tags and headers.
     */
    'nonce_generator' => App\Policies\CspLib\LaravelViteNonceGenerator::class,

    /*
     * Set false to disable automatic nonce generation and handling.
     * This is useful when you want to use 'unsafe-inline' for scripts/styles
     * and cannot add inline nonces.
     * Note that this will make your CSP policy less secure.
     */
    'nonce_enabled' => env('CSP_NONCE_ENABLED', true),

    /**
     * Set to true to include the CSP as a <meta> tag in the site
     * <head> section rather than as a header (useful for large CSPs)
     */
    'use_meta_tag'  => env('CSP_META_TAG', true),
];
