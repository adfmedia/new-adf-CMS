<?php

namespace Modules\WysiwygBlock\Blocks;

use App\Blocks\AbstractBlock;

class Wysiwyg extends AbstractBlock
{
    public static $title        = 'Heading & content';
    public static $description  = 'Contains WYSIWYG content & an optional heading';
    public static $preview      = [
        'src'       => 'previews/wysiwyg.png',
        'alt'       => 'WYSIWYG block preview',
        'width'     => 1561,
        'height'    => 519,
        'module_asset' => true
    ];

    public static $fields       = [
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
            'help'          => 'Set the content for the block',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide some content.'
                ]
            ]
        ]
    ];
}