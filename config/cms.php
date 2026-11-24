<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CMS version
    |--------------------------------------------------------------------------
    |
    | Read from the VERSION file in the site root. Bump it before running
    | php artisan cms:release to build a new release package.
    |
    */

    'version' => trim((string) @file_get_contents(base_path('VERSION'))) ?: '0.0.0',

];
