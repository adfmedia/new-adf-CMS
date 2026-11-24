<?php

namespace Modules\Events\Config;

use Adm\Config\CommonFieldConfig;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventCategory;

class EventsConfig
{
    public static $class        = Event::class;
    public static $url_slug     = 'events';
    public static $title        = 'Events';
    public static $description  = 'A list of all events on the website';
    public static $visual_form  = true;

    public static $listing_columns      = ['title', 'path', 'event_date', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'path', 'categories', 'image', 'excerpt', 'content'],
        'Settings'  => ['status', 'event_date', 'start_time', 'end_time', 'address']
    ];

    public static $fields   = [
        'title'         => [
            'label'         => 'Title',
            'help'          => 'The title of the event',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'path'          => [
            'label'         => 'URL',
            'help'          => 'The event URL',
            'type'          => 'path',
            'prefix'        => [
                'system_page'   => ['controller' => 'event', 'method' => 'index']
            ],
            'url_generation_field'  => 'title',
            'meta_title_gen'        => 'title',
            'meta_description_gen'  => 'excerpt',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a URL'
                ]
            ]
        ],
        'event_date'    => [
            'label'         => 'Date',
            'help'          => 'The date of the event',
            'type'          => 'date',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please set the event\'s date'
                ]
            ]
        ],
        'start_time'    => [
            'label'         => 'Start time',
            'help'          => 'The time the event starts',
            'type'          => 'time',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'end_time'      => [
            'label'         => 'End time',
            'help'          => 'The time the event ends',
            'type'          => 'time',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'address'       => [
            'label'         => 'Address',
            'help'          => 'The address of the event',
            'type'          => 'textarea',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'categories'    => [
            'label'         => 'Categories',
            'help'          => 'Select the categories this event belongs to',
            'type'          => 'orderable_select',
            'relation'      => [
                'to'            => EventCategory::class,
                'storage_table' => 'event_to_event_category'
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'image'         => [
            'label'         => 'Image',
            'help'          => 'Assign images to the event',
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
                    'width'         => 395,
                    'height'        => 415,
                    'style'         => 'fill',
                    'fallback'      => 'desktop'
                ],
                'social_share'  => CommonFieldConfig::SOCIAL_SHARE_IMAGE
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'excerpt'       => [
            'label'         => 'Excerpt',
            'help'          => 'Set a short excerpt for the event, displayed in listings',
            'type'          => 'textarea',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a short excerpt'
                ]
            ]
        ],
        'content'       => [
            'label'         => 'Content',
            'help'          => 'Set the main content for the event page',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide content'
                ]
            ]
        ],
        'status'        => [
            'label'         => 'Publish status',
            'help'          => 'Toggle to green/on to publish this event, making it visible on the website',
            'type'          => 'boolean',
            'enabled_text'  => 'Published',
            'disabled_text' => 'Unpublished',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
    ];
}