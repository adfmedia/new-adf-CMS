<?php

namespace Modules\ContentWithImageBlock\Blocks;

use App\Blocks\AbstractBlock;

class ContentWithImage extends AbstractBlock
{
    public static $title        = 'Content & image';
    public static $description  = 'Contains content, an image, with an optional heading & CTAs';
    public static $preview      = [
        'src'       => 'previews/content-with-image.png',
        'alt'       => 'Content with image block preview',
        'width'     => 1546,
        'height'    => 775,
        'module_asset' => true
    ];

    public static $fields       = [
        'image'     => [
            'label'         => 'Image',
            'help'          => 'Set the image to display',
            'type'          => 'image',
            'sizes'         => [
                'desktop'       => [
                    'label'         => 'Desktop',
                    'width'         => 740,
                    'height'        => 0,
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
            'help'          => 'Set the content to display',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide some content'
                ]
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
        ],
        'image_on_right'    => [
            'label'         => 'Display image on the right',
            'help'          => 'Toggle to green/on to align the image to the right of the content, instead of the left',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];
}