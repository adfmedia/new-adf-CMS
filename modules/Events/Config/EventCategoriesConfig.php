<?php

namespace Modules\Events\Config;

use Modules\Events\Models\EventCategory;

class EventCategoriesConfig
{
    public static $class        = EventCategory::class;
    public static $url_slug     = 'event-categories';
    public static $title        = 'Event categories';
    public static $description  = 'A list of all event categories on the website';
    public static $visual_form  = true;

    public static $listing_columns      = ['title', 'path', 'priority', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'path'],
        'Settings'  => ['status', 'priority']
    ];

    public static $fields   = [
        'title'         => [
            'label'         => 'Title',
            'help'          => 'The title of the category',
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
            'help'          => 'The category URL',
            'type'          => 'path',
            'prefix'        => [
                'system_page'   => ['controller' => 'event', 'method' => 'index']
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
        'priority'      => [
            'label'         => 'Priority',
            'help'          => 'Set the listing priority for this category, with higher numbers appearing first',
            'type'          => 'number',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'status'        => [
            'label'         => 'Publish status',
            'help'          => 'Toggle to green/on to publish this category, making it visible on the website',
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