<?php

namespace Modules\Pages\Config;

use Adm\Config\CommonFieldConfig;
use App\Models\Url;
use Illuminate\Database\Eloquent\Builder;
use Modules\Pages\Models\Page;

class PagesConfig
{
    public static $class        = Page::class;
    public static $url_slug     = 'pages';
    public static $title        = 'Pages';
    public static $description  = 'A list of all the general pages on the website.';
    public static $visual_form  = true;

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['title', 'path', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'path', 'parent_id', 'h1_title', 'page_builder'],
        'Settings'  => ['status'],
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Title',
            'help'          => 'The page title',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'path'  => [
            'label'         => 'URL',
            'help'          => 'The page URL',
            'type'          => 'path',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a URL'
                ]
            ],
            'prefix'        => [
                'relation'      => 'parent_id'
            ],
            'url_generation_field'  => 'title',
            'meta_title_gen'        => 'title',
        ],
        'parent_id' => [
            'label'         => 'Parent page',
            'help'          => 'Select a parent to make this page a child of',
            'type'          => 'relation',
            'relation'      => [
                'to'                => Page::class,
                'exclude_current'   => true,
                'filter_callback'   => 'parentExcludes'
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'h1_title'  => [
            'label'         => 'H1 title',
            'help'          => 'Set a custom H1 title for this page, otherwise the page title will be used',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  [],
                'messages'      =>  []
            ]
        ],
        'status'    => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this page, making it visible to the public',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ],
            'enabled_text'  => 'Published',
            'disabled_text' => 'Unpublished'
        ],

        'page_builder'  => CommonFieldConfig::PAGE_BUILDER_FIELD
    ];

    public static function parentExcludes(Builder $Query): void
    {
        $Home = Url::getSystemPage('home', 'index');
        if ($Home) {
            $Query->where('page_id', '<>', $Home->page_id);
        }
    }

    public static function alterListingItemsQuery(Builder $Query): void
    {
        $Query->whereNotIn('pages.page_id', BlockOnlyPageConfig::getBlockOnlyPageIds());
    }
}