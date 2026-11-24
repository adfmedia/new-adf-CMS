<?php

namespace Modules\News\Config;

use Modules\News\Models\NewsCategory;

class NewsCategoriesConfig
{
    public static $class        = NewsCategory::class;
    public static $url_slug     = 'news-categories';
    public static $title        = 'News categories';
    public static $description  = 'A list of all the news categories on the website.';
    public static $visual_form  = true;

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['title', 'path', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'path', 'h1_title'],
        'Settings'  => ['status'],
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Title',
            'help'          => 'The category title',
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
            'help'          => 'The category URL',
            'type'          => 'path',
            'prefix'        => [
                'system_page'   => ['controller' => 'news', 'method' => 'index']
            ],
            'url_generation_field'  => 'title',
            'meta_title_gen'        => 'title',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a URL'
                ]
            ]
        ],
        'h1_title'  => [
            'label'         => 'H1 title',
            'help'          => 'Set a custom H1 title for this category, otherwise the category title will be used',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  [],
                'messages'      =>  []
            ]
        ],
        'status'    => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this category, making it visible to the public',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ],
            'enabled_text'  => 'Published',
            'disabled_text' => 'Unpublished'
        ]
    ];
}
