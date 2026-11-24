<?php

namespace Adm\Config;

class CommonFieldConfig
{
    /**
     * Common field configuration to keep page builder
     * fields consistent across pages
     * 
     * @var array
     */
    const PAGE_BUILDER_FIELD    = [
        'label'         => 'Page builder',
        'help'          => 'Manage the blocks displayed on this page',
        'type'          => 'page_builder',
        'validation'    => [
            'rules'         => [],
            'messages'      => []
        ]
    ];

    /**
     * Common image configuration to keep social share
     * image sizes consistent across pages
     * 
     * @var array
     */
    const SOCIAL_SHARE_IMAGE    = [
        'label'         => 'Social share',
        'width'         => 1200,
        'height'        => 630,
        'style'         => 'fill',
        'fallback'      => 'desktop'
    ];

    /**
     * Common image configuration to keep social share
     * image sizes, without a fallback, consistent across 
     * pages
     * 
     * @var array
     */
    const SOCIAL_SHARE_IMAGE_NO_FALLBACK    = [
        'label'         => 'Social share',
        'width'         => 1200,
        'height'        => 630,
        'style'         => 'fill',
    ];

    /**
     * Common field configuration to keep grouped text/link
     * fields consistent across pages & blocks
     * 
     * @var array
     */
    const GROUPED_LINK_AND_TEXT_FIELDS = [
        'text'      => [
            'label'     => 'Text',
            'help'      => 'Set the text for the button',
            'type'      => 'string'
        ],
        'link'      => [
            'label'     => 'Link',
            'help'      => 'Set the click-through link for the button',
            'type'      => 'link'
        ]
    ];

    /**
     * Common field configuration to keep grouped text/link/colour
     * fields consistent across pages & blocks
     * 
     * @var array
     */
    const GROUPED_LINK_TEXT_AND_COLOUR_FIELDS = [
        'text'      => [
            'label'     => 'Text',
            'help'      => 'Set the text for the button',
            'type'      => 'string'
        ],
        'link'      => [
            'label'     => 'Link',
            'help'      => 'Set the click-through link for the button',
            'type'      => 'link'
        ],
        'colour'    => [
            'label'     => 'Colour',
            'help'      => 'Select the colour of the button',
            'type'      => 'select',
            'options'   => [
                'primary'       => 'Blue',
                'secondary'     => 'Pink'
            ]
        ]
    ];
}