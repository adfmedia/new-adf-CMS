<?php

namespace Adm\Config;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Model;

class MenuConfig
{
    public static $class        = Menu::class;
    public static $url_slug     = 'menus';
    public static $title        = 'Menus';
    public static $description  = 'A list of all the navigation menus within the site.';

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['title'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'code_reference', 'nest_limit']
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Title',
            'help'          => 'The title for this menu, used as a reference in the CMS',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a menu title'
                ]
            ]
        ],
        'code_reference'    => [
            'label'         => 'Code reference',
            'help'          => 'The reference for this menu, used in the code to retrieve the menu',
            'type'          => 'string',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a code reference'
                ]
            ]
        ],
        'nest_limit'    => [
            'label'         => 'Nesting limit',
            'help'          => 'How many levels of nesting are available for this menu? A value of 1 means no dropdowns, 2 has a single dropdown, 3 has 2 levels, etc.',
            'type'          => 'number',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];

    /**
     * Callback before storing a new menu, used to
     * specify it's language ID
     * 
     * @param Model $Item
     * @return void
     */
    public static function beforeStored(Model $Item): void
    {
        data_set($Item, 'language_id', 1);
    }
}