<?php

namespace Modules\CaseStudies\Config;

use Adm\Config\CommonFieldConfig;
use Modules\CaseStudies\Models\CaseStudy;
use Modules\CaseStudies\Models\CaseStudyCategory;

class CaseStudiesConfig
{
    public static $class        = CaseStudy::class;
    public static $url_slug     = 'case-studies';
    public static $title        = 'Case studies';
    public static $description  = 'A list of all case studies on the website';
    public static $visual_form  = true;

    public static $default_sort             = 'date';
    public static $default_sort_direction   = 'desc';

    public static $listing_columns      = ['title', 'path', 'date', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'path', 'categories', 'h1_title', 'image', 'excerpt', 'content'],
        'Settings'  => ['status', 'date']
    ];

    public static $fields       = [
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
                'system_page'   => ['controller' => 'case_studies', 'method' => 'index']
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
        'categories'    => [
            'label'         => 'Categories',
            'help'          => 'Select the categories this article belongs to',
            'type'          => 'orderable_select',
            'relation'      => [
                'to'            => CaseStudyCategory::class,
                'storage_table' => 'case_study_to_case_study_category',
                //'sort_field'    => 'sort_order'
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
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
        'date'  => [
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