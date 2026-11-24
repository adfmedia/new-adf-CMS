<?php

namespace Modules\News\Config;

use Adm\Config\CommonFieldConfig;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsAuthor;
use Modules\News\Models\NewsCategory;

class NewsArticlesConfig
{
    public static $class        = NewsArticle::class;
    public static $url_slug     = 'news-articles';
    public static $title        = 'News articles';
    public static $description  = 'A list of all news articles on the website';
    public static $visual_form  = true;

    public static $export_button_enabled    = false;

    public static $listing_columns          = ['title', 'path', 'news_category_id', 'news_author_id', 'article_date', 'status'];
    public static $default_sort             = 'article_date';
    public static $default_sort_direction   = 'desc';

    public static $form_field_layout    = [
        'Details'   => ['title', 'path', 'h1_title', 'image', 'excerpt', 'content'],
        'Settings'  => ['status', 'article_date', 'news_category_id', 'news_author_id']
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Title',
            'help'          => 'The article title',
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
            'help'          => 'The article URL',
            'type'          => 'path',
            'prefix'        => [
                'relation'      => 'news_category_id'
            ],
            'base_prefix'   => [
                'system_page'   => ['controller' => 'news', 'method' => 'index']
            ],
            'url_generation_field'  => 'title',
            'meta_title_gen'        => 'title',
            'meta_description_gen'  => 'excerpt',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a URL'
                ]
            ]
        ],
        'h1_title'  => [
            'label'         => 'H1 title',
            'help'          => 'Set a custom H1 title for this article, otherwise the title will be used',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  [],
                'messages'      =>  []
            ]
        ],
        'news_category_id'  => [
            'label'         => 'Category',
            'help'          => 'Select the category this article belongs to',
            'type'          => 'relation',
            'relation'      => [
                'to'            => NewsCategory::class,
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'news_author_id'    => [
            'label'         => 'Author',
            'help'          => 'Select the author who has written this article',
            'type'          => 'relation',
            'relation'      => [
                'to'            => NewsAuthor::class,
                'empty_label'   => 'Select an author',
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'excerpt'   => [
            'label'         => 'Excerpt',
            'help'          => 'Set a short excerpt for the article, displayed in listings',
            'type'          => 'textarea',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a short excerpt'
                ]
            ]
        ],
        'content'   => [
            'label'         => 'Content',
            'help'          => 'Set the main content for the article',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide article content'
                ]
            ]
        ],
        'article_date'  => [
            'label'         => 'Date',
            'help'          => 'Set the article\'s date',
            'type'          => 'date',
            'validation'    => [
                'rules'         => ['required', 'date'],
                'messages'      => [
                    'required'      => 'Please provide a date',
                    'date'          => 'Please provide a valid date'
                ]
            ]
        ],
        'image'     => [
            'label'         => 'Image',
            'help'          => 'Set the image to display for this article',
            'type'          => 'image',
            'sizes'         => [
                'desktop'       => [
                    'label'         => 'Desktop',
                    'width'         => 1150,
                    'height'        => 360,
                    'style'         => 'fill'
                ],
                'mobile'        => [
                    'label'         => 'Mobile',
                    'width'         => 640,
                    'height'        => 300,
                    'style'         => 'fill',
                    'fallback'      => 'desktop'
                ],
                'listing'       => [
                    'label'         => 'Listing',
                    'width'         => 395,
                    'height'        => 415,
                    'style'         => 'fill',
                    'fallback'      => 'desktop'
                ],
                'social_share'  => CommonFieldConfig::SOCIAL_SHARE_IMAGE
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'status'    => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this article, making it visible to the public',
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