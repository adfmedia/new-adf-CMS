<?php

namespace Modules\Pages\Config;

use Adm\Config\CommonFieldConfig;
use App\Models\Url;
use Modules\Pages\Models\Page;

class BlockOnlyPageConfig
{
    public static $skip_class_finder    = true;
    
    public static $class        = Page::class;
    public static $url_slug     = 'block-only-page';
    public static $title        = 'Block Only Page';
    public static $description  = '';

    public static $export_button_enabled    = false;

    public static $listing_columns      = [];
    public static $form_field_layout    = [
        'Blocks'   => ['page_builder', 'title'],
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Title',
            'help'          => 'The page title',
            'type'          => 'hidden',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],

        'page_builder'      => CommonFieldConfig::PAGE_BUILDER_FIELD
    ];

    public static function getBlockOnlyPageIds(): array
    {
        return Url::select('urlable_id')
            ->whereIn('route', [
                'controller=team_member&method=default_blocks'
            ])
            ->where('urlable_type', Page::class)
            ->get()
            ->pluck('urlable_id')
            ->toArray();
    }
}