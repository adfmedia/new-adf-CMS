<?php

namespace Modules\CaseStudies\Config;

use Modules\CaseStudies\Models\CaseStudyCategory;

class CaseStudyCategoriesConfig
{
    public static $class        = CaseStudyCategory::class;
    public static $url_slug     = 'case-study-categories';
    public static $title        = 'Case study categories';
    public static $description  = 'A list of all case study categories on the website';
    public static $visual_form  = true;

    public static $listing_columns      = ['title', 'path', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'path', 'h1_title'],
        'Settings'  => ['status']
    ];

    public static $fields       = [
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
                'system_page'   => ['controller' => 'case_studies', 'method' => 'index']
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
            'help'          => 'Set a custom H1 title for this category, otherwise the title will be used',
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