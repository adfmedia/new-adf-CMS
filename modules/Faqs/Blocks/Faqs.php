<?php

namespace Modules\Faqs\Blocks;

use App\Blocks\AbstractBlock;
use Modules\Faqs\Models\FaqCategory;

class Faqs extends AbstractBlock
{
    public static $title        = 'FAQs';
    public static $description  = 'Add a category of FAQs to the page';
    public static $preview      = [
        'src'       => 'previews/faqs.png',
        'alt'       => 'FAQs block preview',
        'width'     => 1581,
        'height'    => 297,
        'module_asset' => true
    ];

    public static $fields       = [
        'heading'   => [
            'label'         => 'Heading',
            'help'          => 'Optionally add a heading to display above the FAQs',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'content'   => [
            'label'         => 'Content',
            'help'          => 'Optionally add content, displayed above the FAQs',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'faq_category'  => [
            'label'             => 'Category',
            'help'              => 'Select the category of FAQs to display',
            'type'              => 'relation',
            'relation'          => [
                'to'                => FaqCategory::class,
            ],
            'validation'        => [
                'rules'             => ['required'],
                'messages'          => [
                    'required'          => 'Please select a category'
                ]
            ]
        ]
    ];
}