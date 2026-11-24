<?php

namespace Modules\Tests\Config;

use Modules\News\Models\NewsCategory;
use Modules\Pages\Models\Page;
use Modules\Tests\Models\Test;

class TestsConfig
{
    public static $class        = Test::class;
    public static $url_slug     = 'tests';
    public static $title        = 'Tests';
    public static $description  = 'A list of all the testing records on the website.';

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['string'];
    public static $form_field_layout    = [
        'Details'   => ['colour', 'email', 'encrypted', 'image', 'file', 'link', 'multi_relation', 'number', 'orderable_select', 'password', 'page_id', 'repeatable', 'select', 'stacked_repeatable', 'string', 'telephone', 'textarea', 'wysiwyg'],
        'sidebar'   => ['boolean', 'checkbox', 'radio', 'date', 'time', 'date_time']
    ];

    public static $fields   = [
        'boolean'   => [
            'label'         => 'Boolean',
            'help'          => 'Test boolean field',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ],
            'enabled_text'  => 'On',
            'disabled_text' => 'Off'
        ],
        'checkbox'  => [
            'label'         => 'Checkbox',
            'help'          => 'Test checkbox field',
            'type'          => 'checkbox',
            'options'       => ['one' => 'One', '2' => 'Two'],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'colour'    => [
            'label'         => 'Colour',
            'help'          => 'Test colour field',
            'type'          => 'colour',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'date'      => [
            'label'         => 'Date',
            'help'          => 'Test date field',
            'type'          => 'date',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'date_time' => [
            'label'         => 'Date / Time',
            'help'          => 'Test date / time field',
            'type'          => 'date_time',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'email'     => [
            'label'         => 'Email',
            'help'          => 'Test email field',
            'type'          => 'email',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'encrypted' => [
            'label'         => 'Encrypted',
            'help'          => 'Test encrypted field',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'file'      => [
            'label'         => 'File',
            'help'          => 'Test file field',
            'type'          => 'file',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'image'     => [
            'label'         => 'Image',
            'help'          => 'Test image field',
            'type'          => 'image',
            'sizes'         => [
                'desktop'       => [
                    'label'         => 'Desktop',
                    'width'         => 1920,
                    'height'        => 600,
                    'style'         => 'fill'
                ],
                'mobile'        => [
                    'label'         => 'Mobile',
                    'width'         => 640,
                    'height'        => 300,
                    'style'         => 'fit'
                ]
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'link'      => [
            'label'         => 'Link',
            'help'          => 'Example link field',
            'type'          => 'link',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'multi_relation'    => [
            'label'         => 'Multi-relation',
            'help'          => 'Example multi-relation field',
            'type'          => 'orderable_select',
            'relation'      => [
                'to'            => NewsCategory::class,
                'storage_table' => 'tests_to_news_categories',
                'sort_field'    => 'sort_order'
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'number'    => [
            'label'         => 'Number',
            'help'          => 'Example number field',
            'type'          => 'number',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'orderable_select'  => [
            'label'         => 'Orderable Select',
            'help'          => 'Test orderable select field',
            'type'          => 'orderable_select',
            'options'       => [
                '1' => 'One',
                '2' => 'Two',
                '3' => 'Three',
                '4' => 'Four',
                '5' => 'Long title to test searches'
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'password'  => [
            'label'         => 'Password',
            'help'          => 'Test password field',
            'type'          => 'password',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'page_id'   => [
            'label'         => 'Page ID',
            'help'          => 'Test relation field to pages',
            'type'          => 'relation',
            'relation'      => [
                'to'            => Page::class,
                'empty_label'   => 'Select a page',
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'radio'  => [
            'label'         => 'Radio',
            'help'          => 'Test radio field',
            'type'          => 'radio',
            'options'       => ['one' => 'One', '2' => 'Two'],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'repeatable'    => [
            'label'         => 'Repeatable',
            'help'          => 'Test repeatable field',
            'type'          => 'repeatable',
            'fields'        => [
                'image'         => [
                    'label'         => 'Image',
                    'help'          => 'Set the image to display',
                    'type'          => 'image',
                    'sizes'         => [
                        'desktop'       => [
                            'label'         => 'Desktop',
                            'width'         => 320,
                            'height'        => 200,
                            'style'         => 'fill'
                        ],
                        'mobile'        => [
                            'label'         => 'Mobile',
                            'width'         => 320,
                            'height'        => 175,
                            'style'         => 'fill'
                        ]
                    ]
                ],
                'heading'       => [
                    'label'         => 'Heading',
                    'help'          => 'Set the heading for the repeatable element',
                    'type'          => 'string'
                ],
                'snippet'       => [
                    'label'         => 'Snippet',
                    'help'          => 'Set the text snippet for the repeatable element',
                    'type'          => 'textarea'
                ]
            ],
            'add_label'     => 'Add new row',
            'remove_label'  => 'Remove this row',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'stacked_repeatable'    => [
            'label'         => 'Stacked repeatable',
            'help'          => 'Test stacked repeatable field',
            'type'          => 'stacked_repeatable',
            'fields'        => [
                'image'         => [
                    'label'         => 'Image',
                    'help'          => 'Set the image to display',
                    'type'          => 'image',
                    'sizes'         => [
                        'desktop'       => [
                            'label'         => 'Desktop',
                            'width'         => 320,
                            'height'        => 200,
                            'style'         => 'fill'
                        ],
                        'mobile'        => [
                            'label'         => 'Mobile',
                            'width'         => 320,
                            'height'        => 175,
                            'style'         => 'fill'
                        ]
                    ]
                ],
                'heading'       => [
                    'label'         => 'Heading',
                    'help'          => 'Set the heading for the repeatable element',
                    'type'          => 'string'
                ],
                'snippet'       => [
                    'label'         => 'Snippet',
                    'help'          => 'Set the text snippet for the repeatable element',
                    'type'          => 'textarea'
                ],
                'dropdown'      => [
                    'label'         => 'Dropdown option',
                    'help'          => 'Select an option',
                    'type'          => 'select',
                    'options'       => ['Yes' => 'Yes', 'No' => 'No']
                ]
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'select'    => [
            'label'         => 'Select',
            'help'          => 'Test select field',
            'type'          => 'select',
            'options'       => ['one' => 'One', '2' => 'Two'],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'string'    => [
            'label'         => 'String',
            'help'          => 'Test string field',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'telephone' => [
            'label'         => 'Telephone',
            'help'          => 'Test telephone field',
            'type'          => 'telephone',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'textarea'  => [
            'label'         => 'Textarea',
            'help'          => 'Test textarea field',
            'type'          => 'textarea',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'time'      => [
            'label'         => 'Time',
            'help'          => 'Test time field',
            'type'          => 'time',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'wysiwyg'   => [
            'label'         => 'WYSIWYG',
            'help'          => 'Test wysiwyg field',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];
}