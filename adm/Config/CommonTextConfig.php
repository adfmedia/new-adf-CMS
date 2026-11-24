<?php

namespace Adm\Config;

class CommonTextConfig
{
    /**
     * Custom permission slugs, allowing user groups
     * access to different areas of common text
     * 
     * @var array
     */
    public static $permission_slugs = [
        'common-text'   => 'Common text',
        'social-media'  => 'Social media'
    ];

    /**
     * Labelling for common text groups, formatted as:
     *  'group_reference' => ['title' => 'XXX', 'description' => 'YYY']
     * 
     * @var array
     */
    public static $labelling    = [
        'core'      => ['title' => 'Common text',   'description' => 'Set and update various pieces of content across the website here'],
        'social'    => ['title' => 'Social media',  'description' => 'Manage settings related to social media here']
    ];

    /**
     * Common text groups/sections, formatted as:
     *  'group_reference' => [
     *      'Inner section' => ['field_1', 'field_2', 'etc.']
     *  ]
     * 
     * @var array
     */
    public static $groups   = [
        'core'      => [
            'Website defaults'  => ['website_email', 'website_telephone', 'copyright'],
            'Address'           => ['address_company', 'address_line_one', 'address_line_two', 'address_city', 'address_county', 'address_postcode', 'address_country'],
        ],
        'social'    => [
            'Social links'  => ['social_facebook', 'social_twitterx', 'social_linkedin', 'social_youtube', 'social_instagram']
        ]
    ];

    /**
     * All available common text fields, formatted as:
     *  'field_name' => [
     *      'label'     => 'Field label',
     *      'help'      => 'Field help text',
     *      'type'      => 'Field type'
     *  ]
     * 
     * @var array
     */
    public static $fields   = [
        'website_email' => [
            'label'     => 'Website email address',
            'help'      => 'The main website email address',
            'type'      => 'email'
        ],
        'website_telephone' => [
            'label'     => 'Website telephone number',
            'help'      => 'The main website telephone number',
            'type'      => 'telephone'
        ],
        'copyright' => [
            'label'     => 'Footer copyright text',
            'help'      => 'Set the copyright text to display after the year',
            'type'      => 'string'
        ],

        'address_company'   => [
            'label' => 'Company name',
            'help'  => 'Optionally set a company name to display alongside the address',
            'type'  => 'string'
        ],
        'address_line_one'  => [
            'label' => 'First line',
            'help'  => 'Set the first line of address',
            'type'  => 'string'
        ],
        'address_line_two'  => [
            'label' => 'Second line',
            'help'  => 'Optionally set a second line of address',
            'type'  => 'string'
        ],
        'address_city'      => [
            'label' => 'City',
            'help'  => 'Set the city to display in the address',
            'type'  => 'string'
        ],
        'address_county'    => [
            'label' => 'County',
            'help'  => 'Set the county to display in the address',
            'type'  => 'string'
        ],
        'address_postcode'  => [
            'label' => 'Postcode',
            'help'  => 'Set the postcode to display in the address',
            'type'  => 'string'
        ],
        'address_country'   => [
            'label' => 'Country',
            'help'  => 'Set the country to display in the address',
            'type'  => 'string'
        ],

        'social_facebook'   => [
            'label'     => 'Facebook',
            'help'      => 'The Facebook link to include in the footer',
            'type'      => 'string'
        ],
        'social_twitterx'    => [
            'label'     => 'Twitter X',
            'help'      => 'The Twitter X link to include in the footer',
            'type'      => 'string'
        ],
        'social_linkedin'   => [
            'label'     => 'LinkedIn',
            'help'      => 'The LinkedIn link to include in the footer',
            'type'      => 'string'
        ],
        'social_youtube'    => [
            'label'     => 'YouTube',
            'help'      => 'The YouTube link to include in the footer',
            'type'      => 'string'
        ],
        'social_instagram'  => [
            'label'     => 'Instagram',
            'help'      => 'The Instagram link to include in the footer',
            'type'      => 'string'
        ]
    ];
}
