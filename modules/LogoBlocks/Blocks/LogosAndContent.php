<?php

namespace Modules\LogoBlocks\Blocks;

use App\Blocks\AbstractBlock;

class LogosAndContent extends AbstractBlock
{
    public static $title        = 'Logos & content';
    public static $description  = 'Contains a grid of logos, heading, content & optional CTAs';
    public static $preview      = [
        'src'       => 'previews/logos-and-content.png',
        'alt'       => 'Logos and content block preview',
        'width'     => 1291,
        'height'    => 497,
        'module_asset' => true
    ];

    public static $fields       = [
        'heading'       => [
            'label'         => 'Heading',
            'help'          => 'Set the heading to display',
            'type'          => 'string',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please set a heading'
                ]
            ]
        ],
        'content'       => [
            'label'         => 'Content',
            'help'          => 'Set the content to display',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please set some content'
                ]
            ]
        ],
        'buttons'       => [
            'label'         => 'CTA buttons',
            'help'          => 'Optionally add CTAs to the block',
            'type'          => 'repeatable',
            'fields'        => [
                'text'          => [
                    'label'         => 'Text',
                    'help'          => 'Set the text displayed on the CTA',
                    'type'          => 'string'
                ],
                'link'          => [
                    'label'         => 'Link',
                    'help'          => 'Set the click-through link for the CTA',
                    'type'          => 'link'
                ],
                'colour'        => [
                    'label'         => 'Colour',
                    'help'          => 'Select a colour for the CTA',
                    'type'          => 'theme_map',
                    'map_type'      => 'buttons',
                ]
            ],
            'add_label'     => 'Add CTA',
            'remove_label'  => 'Remove CTA',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'logos'         => [
            'label'         => 'Logos',
            'help'          => 'Set the logos to display',
            'type'          => 'repeatable',
            'fields'        => [
                'image'         => [
                    'label'         => 'Logo image',
                    'help'          => 'Select the logo to display',
                    'type'          => 'image',
                    'sizes'         => [
                        'single'        => [
                            'label'         => 'Logo',
                            'width'         => 280,
                            'height'        => 100,
                            'style'         => 'fit'
                        ]
                    ]
                ]
            ],
            'validation'    => [
                'rules'         => ['required', 'array', 'min:1'],
                'messages'      => [
                    'required'      => 'Please set at least 1 logo',
                    'array'         => 'Please set at least 1 logo',
                    'min'           => 'Please set at least 1 logo'
                ]
            ]
        ],
        'logos_on_left' => [
            'label'         => 'Display logos on the left',
            'help'          => 'Toggle to green/on to align the logos to the left of the content, instead of the right',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];
}