<?php

namespace Modules\GlobalBlocks\Config;

use Modules\GlobalBlocks\Models\CommonBlock;
use Adm\Config\CommonFieldConfig;

class CommonBlocksConfig
{
    public static $class             = CommonBlock::class;
    public static $url_slug          = 'common-blocks';
    public static $title             = 'Common Block';
    public static $description       = 'Manage common block content across the site';
    public static $delete_disabled   = true;

    public static $listing_columns = ['title', 'status'];

    public static $form_field_layout = [
        'details'   => ['title', 'page_builder'],
        'sidebar'   => ['status', 'code_reference']
    ];

    public static $fields = [
        'title'         => [
            'label'         => 'Title',
            'help'          => 'Enter a title for the block',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'          => 'Please enter a title for the block'
                ]
            ]
        ],
        'code_reference' => [
            'label'         => 'Code Reference',
            'help'          => 'Enter a code reference for the block',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => [] 
            ]
        ],
        'status'       => [
            'label'            => 'Status',
            'help'             => 'Toggle whether this block is active',
            'type'             => 'boolean',
            'validation'       => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'page_builder' => [
            'label'         => 'Block',
            'help'          => 'Specify a block to use for the common block',
            'type'          => 'page_builder',
            'max'           => 1,
            'settings'      => [
                'hide_common_block' => true
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];
}