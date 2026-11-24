<?php

namespace Modules\Forms\Config;

use Illuminate\Database\Eloquent\Model;
use Modules\Forms\Models\Form;

class FormConfig
{
    public static $class        = Form::class;
    public static $url_slug     = 'forms';
    public static $title        = 'Forms';
    public static $description  = 'A list of all the forms on the website.';

    public static $permission_slugs = [
        'form-submissions'  => 'Form submissions',
    ];

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['title', 'status'];
    public static $form_field_layout    = [        
        'Content'           => ['title', 'heading', 'content'],
        'Fields'            => ['structure', 'submit_text', 'marketing_opt_in', 'marketing_opt_in_text', 'marketing_opt_out_text', 'use_recaptcha', 'redirect_link'],
        'Internal email'    => ['internal_recipients', 'internal_subject'],
        'Auto-response'     => ['send_auto_response', 'auto_response_subject', 'auto_response_content', 'auto_response_attachment'],
        'Super-admin'       => ['code_reference', 'template'],
        'sidebar'           => ['status', 'ga4_event'],
    ];

    public static $fields   = [
        'title' => [
            'label'         => 'Title',
            'help'          => 'The form title, used in the CMS as a reference',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'heading'   => [
            'label'         => 'Form heading',
            'help'          => 'Set the heading for the form, or leave empty to use the title above',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'content'   => [
            'label'         => 'Form content',
            'help'          => 'Set the content to display above the form',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],

        'structure'                 => [
            'label'         => 'Form structure',
            'help'          => 'Manage the structure and fields displayed in the form',
            'type'          => 'form_structure',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'submit_text'               => [
            'label'         => 'Submit button text',
            'help'          => 'Set the text to display on the form\'s submit button, or leave empty to show \'Submit\'',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'marketing_opt_in'          => [
            'label'         => 'Marketing opt-in',
            'help'          => 'Toggle to green/on to include a marketing opt-in checkbox on the form',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'marketing_opt_in_text'     => [
            'label'         => 'Marketing opt-in text',
            'help'          => 'Set the text to display alongside the opt-in checkbox',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'marketing_opt_out_text'    => [
            'label'         => 'Marketing opt-out text',
            'help'          => 'Set the opt-out text to display alongside the opt-in checkbox',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'use_recaptcha'             => [
            'label'         => 'Use Recaptcha',
            'help'          => 'Toggle to green/on to add Recaptcha spam protection to the form',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'redirect_link'             => [
            'label'         => 'Redirect link',
            'help'          => 'Optionally set a link to redirect the user to when they complete the form',
            'type'          => 'link',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],

        'internal_recipients'   => [
            'label'         => 'Recipient email addresses',
            'help'          => 'Set the email addresses to send form submission notifications to, separated by commas',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'internal_subject'      => [
            'label'         => 'Subject',
            'help'          => 'Set the subject line for the internal email',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],

        'send_auto_response'        => [
            'label'         => 'Send email',
            'help'          => 'Toggle to green/on to send an email to the user completing the form (if an email address is provided)',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'auto_response_subject'     => [
            'label'         => 'Subject',
            'help'          => 'Set the subject to use for the form\'s auto-response email',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'auto_response_content'     => [
            'label'         => 'Content',
            'help'          => 'Set the content for the form\'s auto-response email',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'auto_response_attachment'  => [
            'label'         => 'Attachment',
            'help'          => 'Select an attachment to send with the auto-response email',
            'type'          => 'file',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],

        'code_reference'        => [
            'label'         => 'Code reference',
            'help'          => 'Set a refernce for this form, used to retrieve it within the code',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'template'              => [
            'label'         => 'Template',
            'help'          => 'Select the template file to use for this form',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],

        'status'    => [
            'label'         => 'Publish status',
            'help'          => 'Toggle on to publish this form, making it visible to the public',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ],
            'enabled_text'  => 'Published',
            'disabled_text' => 'Unpublished'
        ],
        'ga4_event' => [
            'label'         => 'GA4 event',
            'help'          => 'Set the event to send to GA4 when this form is submitted',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];

    public static function dynamicChangeFormLayout(array $form_field_layout, ?int $item_id): array
    {
        if (empty(config('services.recaptcha.site_key'))) {
            foreach ($form_field_layout['Fields'] as $idx => $field) {
                if ($field == 'use_recaptcha') {
                    unset($form_field_layout['Fields'][$idx]);
                }
            }
        }

        return $form_field_layout;
    }
}