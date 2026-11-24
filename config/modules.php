<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available modules
    |--------------------------------------------------------------------------
    |
    | The modules this codebase ships with. Since Phase 2 the module registry
    | (the `modules` table, managed with the cms:module:* commands) decides
    | which are enabled. This list is only used before the registry exists:
    | to fill it on first migration, and while the installer runs.
    |
    | At runtime config('modules.available') holds the enabled modules and
    | config('modules.declared') holds this list.
    |
    */

    'available' => [
        'BannerBlock',
        'CaseStudies',
        'ContentWithImageBlock',
        'CtaTilesBlock',
        'GlobalBlocks',
        'Events',
        'Faqs',
        'Forms',
        'LogoBlocks',
        'News',
        'Offices',
        'Pages',
        'Search',
        'TeamMembers',
        'Testimonials',
        'VideoBlock',
        'WysiwygBlock',

        'Tests',
    ]
];