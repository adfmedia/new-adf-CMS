<?php

namespace Modules\Faqs\Config;

use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;

class FaqCategoriesConfig
{
    public static $class        = FaqCategory::class;
    public static $url_slug     = 'faq-categories';
    public static $title        = 'FAQ categories';
    public static $description  = 'A list of all FAQ categories on the website';

    public static $add_button_text      = 'Create FAQ category';
    public static $search_placeholder   = 'Search FAQ categories';

    public static $listing_columns      = ['title', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'faqs'],
        'sidebar'   => ['status'],
    ];

    public static $fields   = [
        'title'         => [
            'label'         => 'Title',
            'help'          => 'The title of this category',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'faqs'      => [
            'label'         => 'FAQs',
            'help'          => 'Select the FAQs belonging to this category',
            'type'          => 'orderable_select',
            'relation'      => [
                'to'            => Faq::class,
                'storage_table' => 'faq_to_faq_category',
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'status'        => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this category, making it visible on the website',
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
