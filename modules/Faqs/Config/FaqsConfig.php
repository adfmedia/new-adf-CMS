<?php

namespace Modules\Faqs\Config;

use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;

class FaqsConfig
{
    public static $class        = Faq::class;
    public static $url_slug     = 'faqs';
    public static $title        = 'FAQs';
    public static $description  = 'A list of all FAQs on the website';

    public static $add_button_text      = 'Create FAQ';
    public static $search_placeholder   = 'Search FAQs';

    public static $listing_columns      = ['title', 'priority', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'question', 'answer', 'categories'],
        'sidebar'   => ['status', 'priority'],
    ];

    public static $fields   = [
        'title'         => [
            'label'         => 'Title / reference',
            'help'          => 'A title or reference for this FAQ, used in the CMS',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'question'      => [
            'label'         => 'Question',
            'help'          => 'The frequently asked question',
            'type'          => 'string',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a question'
                ]
            ]
        ],
        'answer'        => [
            'label'         => 'Answer',
            'help'          => 'The answer to the question',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide an answer'
                ]
            ]
        ],
        'categories'    => [
            'label'         => 'Categories',
            'help'          => 'Select the categories this FAQ belongs to',
            'type'          => 'orderable_select',
            'relation'      => [
                'to'            => FaqCategory::class,
                'storage_table' => 'faq_to_faq_category',
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'priority'      => [
            'label'         => 'Listing priority',
            'help'          => 'Set the priority at which this FAQ should appear in listings - higher values appear first',
            'type'          => 'number',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'status'        => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this FAQ, making it visible on the website',
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