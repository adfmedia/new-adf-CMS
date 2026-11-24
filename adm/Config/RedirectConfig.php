<?php

namespace Adm\Config;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class RedirectConfig
{
    public static $class        = Redirect::class;
    public static $url_slug     = 'redirects';
    public static $title        = 'Redirects';
    public static $description  = 'A list of all the redirects on the website.';

    public static $export_button_enabled    = true;

    public static $listing_columns      = ['old_url', 'new_url', 'redirect_type', 'status'];
    public static $form_field_layout    = [
        'Details'   => ['old_url', 'new_url'],
        'sidebar'   => ['status', 'redirect_type', 'no_match_only']
    ];

    public static $fields   = [
        'old_url'       => [
            'label'         => 'Redirect from',
            'help'          => 'Set the URL to redirect from. Please note you can include an asterisk for a wildcard section, e.g. news/*.',
            'type'          => 'string',
            'validation'    => [
                'rules'         => ['required', 'unique:redirects'],
                'update_rules'  => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a from URL',
                    'unique'        => 'That from URL already has a redirect assigned'
                ]
            ]
        ],
        'new_url'       => [
            'label'         => 'Redirect to',
            'help'          => 'Set the URL to redirect to. Please note you can also include an asterisk here to transfer the wildcard over, e.g. if using news/* above and latest-news/* here, an old URL of news/my-category will redirect to latest-news/my-category.',
            'type'          => 'string',
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a to URL'
                ]
            ]
        ],
        'redirect_type' => [
            'label'         => 'Redirect type',
            'help'          => 'Select the type of redirect to use',
            'type'          => 'select',
            'options'       => [301 => 'Permanent (301)', 302 => 'Temporary (302)'],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'no_match_only' => [
            'label'         => 'Only redirect if page not found',
            'help'          => 'Toggle to green/on to only trigger this redirect if the URL doesn\'t match an existing page',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'status'        => [
            'label'         => 'Status',
            'help'          => 'Toggle whether this redirect is active or not',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];

    public static $extra_actions    = [
        [
            'slug'  => 'import-redirects',
            'text'  => 'Import redirects',
            'class' => 'btn btn--grey',
            'super_admin_only'  => true
        ]
    ];

    /**
     * Callback to include additional validation rules when updating
     * a redirect
     * 
     * @param array $rules
     * @param array $messages
     * @param Model $Item
     * @return array
     */
    public static function getUpdateValidation(array $rules, array $messages, Model $Item): array
    {
        $rules['old_url'][] = Rule::unique($Item->getTable())->ignore($Item);

        return [$rules, $messages];
    }
}