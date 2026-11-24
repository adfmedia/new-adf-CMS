<?php

namespace Modules\CtaTilesBlock\Blocks;

use Adm\Config\CommonFieldConfig;
use App\Blocks\AbstractBlock;
use App\Models\PageBuilderBlock;
use Illuminate\Database\Eloquent\Model;

class CtaTiles extends AbstractBlock
{
    public static $title        = 'CTA Tiles';
    public static $description  = 'Contains a repeatable set of image, heading & CTA';
    public static $preview      = [
        'src'       => 'images/block-previews/cta-tiles.png',
        'alt'       => 'CTA tiles block preview',
        'width'     => 1559,
        'height'    => 395,
        'viteable'  => true
    ];

    public static $fields       = [
        'tiles'     => [
            'label'         => 'Tiles',
            'help'          => 'Set the tiles to display',
            'type'          => 'repeatable',
            'fields'        => [
                'image'         => [
                    'label'         => 'Image',
                    'help'          => 'Select the image to display',
                    'type'          => 'image',
                    'sizes'         => [
                        'single'        => [
                            'label'         => 'Image',
                            'width'         => 490,
                            'height'        => 250,
                            'style'         => 'fill'
                        ]
                    ]
                ],
                'heading'       => [
                    'label'         => 'Heading',
                    'help'          => 'Set the heading for the tile',
                    'type'          => 'string'
                ],
                'cta'       => [
                    'label'     => 'CTA button',
                    'help'      => 'Set the click-through destination & text for the CTA button',
                    'type'      => 'grouped',
                    'fields'    => CommonFieldConfig::GROUPED_LINK_AND_TEXT_FIELDS
                ]
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];

    protected function processContents(array $content, Model $Model, PageBuilderBlock $Block): array
    {
        if (!empty($content['tiles'])) {
            foreach ($content['tiles'] as $idx => $tile) {
                if (
                    !data_get($tile, 'image.single.src') &&
                    !data_get($tile, 'heading') &&
                    !data_get($tile, 'cta.link.url') &&
                    !data_get($tile, 'cta.text')
                ) {
                    unset($content['tiles'][$idx]);
                }
            }
        }

        return $content;
    }
}