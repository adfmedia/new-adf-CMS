<?php

namespace Adm\Config;

use Adm\Models\AdmUser;
use Adm\Models\AdmUserGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Writer as QRWriter;
use BaconQrCode\Renderer\ImageRenderer as QRRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle as QRRendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd as QRRendererImage;

class AdminUserConfig
{
    public static $class        = AdmUser::class;
    public static $url_slug     = 'admin-users';
    public static $title        = 'Admin users';
    public static $description  = 'A list of all the admin users who can log in to the CMS.';

    public static $export_button_enabled    = true;

    public static $listing_columns      = ['name', 'email', 'adm_user_group_id'];
    public static $form_field_layout    = [
        'Details'   => ['name', 'email', 'password'],
        'sidebar'   => ['adm_user_group_id', 'mfa_qr_code']
    ];

    public static $fields   = [
        'name'  => [
            'label'         => 'Name',
            'help'          => 'The user\'s name',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         =>  ['required'],
                'messages'      =>  [
                    'required'      => 'Please provide a name'
                ]
            ]
        ],
        'email' => [
            'label'         => 'Email address',
            'help'          => 'The user\'s email address, used to login',
            'type'          => 'email',
            'validation'    => [
                'rules'         => ['required', 'email', 'unique:adm_users'],
                'update_rules'  => ['required', 'email'],
                'messages'      => [
                    'required'      => 'Please provide an email address',
                    'email'         => 'Please provide a valid email address',
                    'unique'        => 'Sorry, that email address is already taken'
                ]
            ]
        ],
        'password'  => [
            'label'         => 'Password',
            'help'          => 'The user\'s password. Leave empty to keep current password if editing a user.',
            'type'          => 'password',
            'validation'    => [
                'rules'         => ['required'],
                'update_rules'  => [],
                'messages'      => [
                    'required'      => 'Please provide a password'
                ],
                'bypass_update_required'    => true,
            ]
        ],
        'adm_user_group_id' => [
            'label'         => 'User group',
            'help'          => 'Select the group this user belongs to, used to determine their permissions within the CMS.',
            'type'          => 'relation',
            'relation'      => [
                'to'            => AdmUserGroup::class,
                'empty_label'   => 'Select a group',
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'mfa_qr_code'   => [
            'label'             => 'Two-factor QR code',
            'help'              => 'Scan the QR code below to add to your authenticator app',
            'type'              => 'generic_output',
            'generate_method'   => [self::class, 'getMfaQrCode'],
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
    ];

    public static $extra_actions    = [
        [
            'slug'  => 'admin-user-groups',
            'text'  => 'Manage user groups',
            'class' => 'btn btn--grey'
        ]
    ];

    /**
     * Callback to tweak the query used to return list view results,
     * removing super admin users from the list if the current user
     * is not a super admin
     * 
     * @param Builder $Query
     * @return void
     */
    public static function alterListingItemsQuery(Builder $Query): void
    {
        $User = Auth::guard('adm')->user();
        if (!$User->is_super_admin) {
            $super_admin_group_ids = AdmUserGroup::where('super_admin', true)->get()->pluck('id')->toArray();
            $Query->whereNotIn('adm_user_group_id', $super_admin_group_ids);
        }
    }

    /**
     * Callback to include additional validation rules when updating
     * a user
     * 
     * @param array $rules
     * @param array $messages
     * @param Model $Item
     * @return array
     */
    public static function getUpdateValidation(array $rules, array $messages, Model $Item): array
    {
        $rules['email'][] = Rule::unique($Item->getTable())->ignore($Item);

        return [$rules, $messages];
    }

    /**
     * Custom authentication check, allowing users to update
     * their individual profiles while not having access to
     * the wider admin users section
     * 
     * @param ?string $method
     * @param ?int $id
     * @return bool
     */
    public static function authCheck(?string $method, ?int $id): bool
    {
        /** @var \Adm\Models\AdmUser */
        $User = Auth::guard('adm')->user();

        if (
            $id == $User->id &&
            in_array($method, ['edit', 'update'])
        ) {
            return true;
        }

        return false;
    }

    public static function dynamicChangeFormLayout(array $form_layout, mixed $item_id): array
    {
        $User = AdmUser::find($item_id);
        if (!config('auth.mfa.adm.enabled') || !$User || $User->id != Auth::guard('adm')->user()->id) {
            foreach ($form_layout as $group => $fields) {
                foreach ($fields as $idx => $field) {
                    if ($field == 'mfa_qr_code') {
                        unset($form_layout[$group][$idx]);
                    }
                }
            }
        }

        return $form_layout;
    }

    public static function getMfaQrCode(AdmUser $User)
    {
        $MFA = new Google2FA();
        $QRWriter = new QRWriter(new QRRenderer(new QRRendererStyle(300), new QRRendererImage()));
        $qrUrl = $MFA->getQRCodeUrl(config('app.name'), $User->email, $User->mfa_secret);
        $qrSvg = $QRWriter->writeString($qrUrl);
        data_set($User, 'mfa_qr_code', $qrSvg);
    }
}