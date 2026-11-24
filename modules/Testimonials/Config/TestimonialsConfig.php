<?php

namespace Modules\Testimonials\Config;

use Modules\Testimonials\Models\Testimonial;

class TestimonialsConfig
{
    public static $class        = Testimonial::class;
    public static $url_slug     = 'testimonials';
    public static $title        = 'Testimonials';
    public static $description  = 'A list of all testimonials on the website';

    public static $listing_columns      = ['title', 'citation', 'job_title', 'date', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'quote', 'citation', 'job_title', 'image'],
        'sidebar'   => ['status', 'date']
    ];

    public static $fields   = [
        'title'     => [
            'label'         => 'Title / reference',
            'help'          => 'The title of this testimonial, used as a reference in the CMS',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'quote'     => [
            'label'         => 'Quote',
            'help'          => 'The quote / testimonial to display',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a quote'
                ]
            ]
        ],
        'citation'  => [
            'label'         => 'Citation',
            'help'          => 'The citation to display for this testimonial',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a citation'
                ]
            ]
        ],
        'job_title' => [
            'label'         => 'Job title',
            'help'          => 'Optionally add a job title to the citation',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  [],
                'messages'      =>  []
            ]
        ],
        'image'     => [
            'label'         => 'Image',
            'help'          => 'Set the image to display for this testimonial',
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
                ]
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'date'      => [
            'label'         => 'Date',
            'help'          => 'Set the date of this testimonial, used for ordering purposes',
            'type'          => 'date',
            'validation'    => [
                'rules'         => ['required', 'date'],
                'messages'      => [
                    'required'      => 'Please provide a date',
                    'date'          => 'Please provide a valid date'
                ]
            ]
        ],
        'status'    => [
            'label'         => 'Publish Status',
            'help'          => 'Toggle on to publish this testimonial, making it visible to the public',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ],
            'enabled_text'  => 'Published',
            'disabled_text' => 'Unpublished'
        ]
    ];
}