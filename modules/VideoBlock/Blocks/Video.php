<?php

namespace Modules\VideoBlock\Blocks;

use App\Blocks\AbstractBlock;

class Video extends AbstractBlock
{
    public static $title        = 'Popup video';
    public static $description  = 'Add a block containing an image, heading & playable video to the page';
    public static $preview      = [
        'src'       => 'previews/video.png',
        'alt'       => 'Video block preview',
        'width'     => 1565,
        'height'    => 614,
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
            'help'          => 'Set the heading to display in the block',
            'type'          => 'string',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please set a heading'
                ]
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
        'video'     => [
            'label'         => 'Video URL',
            'help'          => 'Enter the URL of the video to play',
            'type'          => 'string',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please set a video URL'
                ]
            ]
        ]
    ];
}