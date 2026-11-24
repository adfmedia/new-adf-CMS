<?php

namespace Modules\LogoBlocks\Blocks;

use App\Blocks\AbstractBlock;

class LogoGrid extends AbstractBlock
{
    public static $title        = 'Logo grid';
    public static $description  = 'Contains a grid of logos';
    public static $preview      = [
        'src'       => 'previews/logo-grid.png',
        'alt'       => 'Logo grid block preview',
        'width'     => 1266,
        'height'    => 356,
        'module_asset' => true
    ];

    public static $fields       = [
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
        ]
    ];
}
