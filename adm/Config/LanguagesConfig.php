<?php

namespace Adm\Config;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class LanguagesConfig
{
    public static $class        = Language::class;
    public static $url_slug     = 'languages';
    public static $title        = 'Languages';
    public static $description  = 'A list of all the languages the website is available in.';

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['title', 'locale', 'base_url'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'locale', 'base_url']
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Title',
            'help'          => 'The language\'s title',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'locale'    => [
            'label'         => 'Locale',
            'help'          => 'The language\'s locale',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  ['required', 'unique:languages'],
                'update_rules'  =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a locale',
                    'unique'        => 'That locale is already in use'
                ]
            ]
        ],
        'base_url'  => [
            'label'         => 'Base URL',
            'help'          => 'The language\'s base URL',
            'type'          => 'string',
            'validation'    => [
                'rules'         =>  ['required', 'unique:languages'],
                'update_rules'  =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a base URL',
                    'unique'        => 'That base URL is already in use'
                ]
            ]
        ],
    ];

    /**
     * Callback to include additional validation rules when updating
     * a language
     * 
     * @param array $rules
     * @param array $messages
     * @param Model $Item
     * @return array
     */
    public static function getUpdateValidation(array $rules, array $messages, Model $Item): array
    {
        $rules['locale'][] = Rule::unique($Item->getTable())->ignore($Item);
        $rules['base_url'][] = Rule::unique($Item->getTable())->ignore($Item);

        return [$rules, $messages];
    }
}