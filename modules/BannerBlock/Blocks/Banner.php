<?php

namespace Modules\BannerBlock\Blocks;

use App\Blocks\AbstractBlock;

class Banner extends AbstractBlock
{
    public static $title        = 'Banner';
    public static $description  = 'Contains an image, heading & optional CTAs';
    public static $preview      = [
        'src'       => 'images/block-previews/banner.png',
        'alt'       => 'Banner block preview',
        'width'     => 1899,
        'height'    => 517,
        'viteable'  => true
    ];

    public static $fields       = [
        'image'     => [
            'label'         => 'Image',
            'help'          => 'Set the image to display in the banner',
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
                    'height'        => 400,
                    'style'         => 'fill',
                    'fallback'      => 'desktop'
                ]
            ]
        ],
        'heading'   => [
            'label'         => 'Heading',
            'help'          => 'Optionally add a heading to the block',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'content'   => [
            'label'         => 'Content',
            'help'          => 'Optionally add content to the block',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'buttons'   => [
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
        ]
    ];
}