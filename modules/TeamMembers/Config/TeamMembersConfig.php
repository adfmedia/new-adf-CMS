<?php

namespace Modules\TeamMembers\Config;

use Adm\Config\CommonFieldConfig;
use Modules\TeamMembers\Models\TeamMember;

class TeamMembersConfig
{
    public static $class        = TeamMember::class;
    public static $url_slug     = 'team-members';
    public static $title        = 'Team members';
    public static $description  = 'A list of all team members on the website';
    public static $visual_form  = true;

    public static $listing_columns      = ['first_name', 'last_name', 'path', 'priority', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['first_name', 'last_name', 'path', 'image', 'job_title', 'email', 'telephone', 'social_links', 'short_description', 'content', 'page_builder'],
        'Settings'  => ['status', 'priority']
    ];

    public static $fields   = [
        'first_name'        => [
            'label'             => 'First name',
            'help'              => 'The team member\'s first name',
            'type'              => 'string',
            'edit_linked'       => true,
            'validation'        => [
                'rules'             => ['required'],
                'messages'          => [
                    'required'          => 'Please provide a first name'
                ]
            ]
        ],
        'last_name'         => [
            'label'             => 'Last name',
            'help'              => 'The team member\'s last name',
            'type'              => 'string',
            'edit_linked'       => true,
            'validation'        => [
                'rules'             => ['required'],
                'messages'          => [
                    'required'          => 'Please provide a last name'
                ]
            ]
        ],
        'path'              => [
            'label'             => 'URL',
            'help'              => 'The team member\'s URL',
            'type'              => 'path',
            'prefix'            => [
                'system_page'       => ['controller' => 'team_member', 'method' => 'index']
            ],
            'url_generation_field'      => ['first_name', 'last_name'],
            'meta_title_gen'            => ['first_name', 'last_name'],
            'meta_description_gen'      => 'short_description',
            'validation'        => [
                'rules'             => ['required'],
                'messages'          => [
                    'required'          => 'Please provide a URL'
                ]
            ]
        ],
        'image'             => [
            'label'             => 'Image',
            'help'              => 'Set the image to display for this team member',
            'type'              => 'image',
            'sizes'             => [
                'desktop'           => [
                    'label'             => 'Desktop',
                    'width'             => 1150,
                    'height'            => 360,
                    'style'             => 'fill'
                ],
                'mobile'            => [
                    'label'             => 'Mobile',
                    'width'             => 640,
                    'height'            => 300,
                    'style'             => 'fill',
                    'fallback'          => 'desktop'
                ],
                'listing'           => [
                    'label'             => 'Listing',
                    'width'             => 445,
                    'height'            => 300,
                    'style'             => 'fill',
                    'fallback'          => 'desktop'
                ]
            ],
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'job_title'         => [
            'label'             => 'Job title',
            'help'              => 'The team member\'s job title',
            'type'              => 'string',
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'email'             => [
            'label'             => 'Email address',
            'help'              => 'The team member\'s email address',
            'type'              => 'email',
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'telephone'         => [
            'label'             => 'Telephone number',
            'help'              => 'The team member\'s telephone number',
            'type'              => 'telephone',
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'social_links'      => [
            'label'             => 'Social links',
            'help'              => 'Add links to the team member\'s social profiles',
            'type'              => 'repeatable',
            'fields'            => [
                'platform'          => [
                    'label'             => 'Platform',
                    'help'              => 'Select the social platform to link to, used to control the icon displayed',
                    'type'              => 'select',
                    'options'           => ['linkedin' => 'LinkedIn', 'x' => 'X (Twitter)', 'facebook' => 'Facebook']
                ],
                'link'              => [
                    'label'             => 'Link / URL',
                    'help'              => 'Set the URL link to add to the team member',
                    'type'              => 'string'
                ]
            ],
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'short_description' => [
            'label'             => 'Short description',
            'help'              => 'A short description of this team member, displayed in listings',
            'type'              => 'textarea',
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'content'           => [
            'label'             => 'Page content',
            'help'              => 'Set the main content to display on this team member\'s page',
            'type'              => 'wysiwyg',
            'validation'        => [
                'rules'             => ['required'],
                'messages'          => [
                    'required'          => 'Please provide some content'
                ]
            ]
        ],
        'priority'          => [
            'label'             => 'Priority',
            'help'              => 'Set the listing priority for this team member, with higher numbers appearing first',
            'type'              => 'number',
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'status'            => [
            'label'             => 'Publish Status',
            'help'              => 'Toggle on to publish this article, making it visible to the public',
            'type'              => 'boolean',
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ],
            'enabled_text'      => 'Published',
            'disabled_text'     => 'Unpublished'
        ],
        'page_builder'      => CommonFieldConfig::PAGE_BUILDER_FIELD
    ];
}