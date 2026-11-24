<?php

namespace Modules\News\Config;

use Modules\News\Models\NewsAuthor;

class NewsAuthorsConfig
{
    public static $class        = NewsAuthor::class;
    public static $url_slug     = 'news-authors';
    public static $title        = 'News authors';
    public static $description  = 'A list of all the news authors on the website.';

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['title', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'image'],
        'sidebar'   => ['status'],
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Name',
            'help'          => 'The author\'s name',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'image'     => [
            'label'         => 'Image',
            'help'          => 'Set the image to display for this author',
            'type'          => 'image',
            'sizes'         => [
                'single'        => [
                    'label'         => 'Image',
                    'width'         => 200,
                    'height'        => 200,
                    'style'         => 'fill'
                ],
                'listing'       => [
                    'label'         => '',
                    'width'         => 24,
                    'height'        => 24,
                    'style'         => 'fill',
                    'fallback'      => 'single',
                    'cms_hide'      => true
                ]
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'status'    => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this author, making it visible to the public',
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
