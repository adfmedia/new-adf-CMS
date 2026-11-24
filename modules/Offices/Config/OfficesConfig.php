<?php

namespace Modules\Offices\Config;

use Adm\Config\CommonFieldConfig;
use Modules\Offices\Models\Office;

class OfficesConfig
{
    public static $class        = Office::class;
    public static $url_slug     = 'offices';
    public static $title        = 'Offices';
    public static $description  = 'A list of all offices on the website';
    public static $visual_form  = true;

    public static $listing_columns      = ['title', 'path', 'priority', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'path', 'h1_title', 'image', 'email', 'telephone', 'address', 'opening_times', 'page_builder'],
        'Settings'  => ['status', 'priority', 'latitude', 'longitude']
    ];

    public static $fields   = [
        'title'         => [
            'label'         => 'Title',
            'help'          => 'The office title',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'path'          => [
            'label'         => 'URL',
            'help'          => 'The office URL',
            'type'          => 'path',
            'prefix'        => [
                'system_page'   => ['controller' => 'office', 'method' => 'index']
            ],
            'url_generation_field'  => 'title',
            'meta_title_gen'        => 'title',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a URL'
                ]
            ]
        ],
        'h1_title'      => [
            'label'         => 'H1 title',
            'help'          => 'Set a custom H1 title for this office, otherwise the title will be used',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  [],
                'messages'      =>  []
            ]
        ],
        'image'         => [
            'label'         => 'Image',
            'help'          => 'Set the image to display for this office',
            'type'          => 'image',
            'sizes'         => [
                'desktop'       => [
                    'label'         => 'Desktop',
                    'width'         => 1150,
                    'height'        => 360,
                    'style'         => 'fill'
                ],
                'mobile'        => [
                    'label'         => 'Mobile',
                    'width'         => 640,
                    'height'        => 300,
                    'style'         => 'fill',
                    'fallback'      => 'desktop'
                ],
                'listing'       => [
                    'label'         => 'Listing',
                    'width'         => 510,
                    'height'        => 300,
                    'style'         => 'fill',
                    'fallback'      => 'desktop'
                ]
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'email'         => [
            'label'         => 'Email address',
            'help'          => 'Add an email address to show alongside this office',
            'type'          => 'email',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'telephone'     => [
            'label'         => 'Telephone number',
            'help'          => 'Add a telephone number to show alongside this office',
            'type'          => 'telephone',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'address'       => [
            'label'         => 'Address',
            'help'          => 'Add an address to show alongside this office',
            'type'          => 'textarea',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'opening_times' => [
            'label'         => 'Opening times',
            'help'          => 'Add opening times to show alongside this office',
            'type'          => 'repeatable',
            'fields'        => [
                'days'          => [
                    'label'         => 'Day(s)',
                    'help'          => 'The day(s) to display',
                    'type'          => 'string'
                ],
                'times'         => [
                    'label'         => 'Times',
                    'help'          => 'The times to display',
                    'type'          => 'string'
                ]
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'latitude'      => [
            'label'         => 'Latitude',
            'help'          => 'Set the latitude position of this office, used when displayed on a map',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'longitude'     => [
            'label'         => 'Longitude',
            'help'          => 'Set the longitude position of this office, used when displayed on a map',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'priority'      => [
            'label'         => 'Priority',
            'help'          => 'Set the listing priority for this office, with higher numbers appearing first',
            'type'          => 'number',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'status'        => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this office, making it visible to the public',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ],
            'enabled_text'  => 'Published',
            'disabled_text' => 'Unpublished'
        ],
        'page_builder'  => CommonFieldConfig::PAGE_BUILDER_FIELD
    ];
}