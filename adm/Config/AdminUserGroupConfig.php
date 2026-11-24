<?php

namespace Adm\Config;

use Adm\Models\AdmUserGroup;
use App\Helpers\ClassFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;

class AdminUserGroupConfig
{
    public static $class        = AdmUserGroup::class;
    public static $url_slug     = 'admin-user-groups';
    public static $title        = 'Admin user groups';
    public static $description  = 'A list of all the admin user groups in the CMS.';

    public static $export_button_enabled    = false;

    public static $listing_columns      = ['title', 'super_admin', 'active'];
    public static $form_field_layout    = [
        'Details'   => ['title', 'permissions'],
        'sidebar'   => ['active', 'super_admin']
    ];

    public static $fields   = [
        'title'         => [
            'label'         => 'Title',
            'help'          => 'The title of this user group, used as a reference in the CMS',
            'type'          => 'string',
            'edit_linked'   => true,
            'validation'    => [
                'rules'         => ['required'],
                'messages'      => [
                    'required'      => 'Please provide a title'
                ]
            ]
        ],
        'permissions'   => [
            'label'             => 'Permissions',
            'help'              => 'Set the areas of the CMS users in this group can access',
            'type'              => 'orderable_select',
            'generate_options'  => 'getPermissionOptions',
            'include_move_all'  => true,
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ],
        'super_admin'   => [
            'label'         => 'Super admin',
            'help'          => 'Toggle on to make this group a super admin group, with access to everything',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'active'        => [
            'label'         => 'Active',
            'help'          => 'Toggle on to make this group active, allowing assigned users to log in',
            'type'          => 'boolean',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];

    public static $extra_actions    = [
        [
            'slug'  => 'admin-users',
            'text'  => 'Manage users',
            'class' => 'btn btn--grey'
        ]
    ];

    /**
     * Callback to tweak the query used to return list view results,
     * removing super admin groups from the list if the current user
     * is not a super admin
     * 
     * @param Builder $Query
     * @return void
     */
    public static function alterListingItemsQuery(Builder $Query): void
    {
        $User = Auth::guard('adm')->user();
        if (!$User->is_super_admin) {
            $Query->where('super_admin', false);
        }
    }

    /**
     * Dynamically change the form field layout, removing the
     * super admin option if the user is not a super admin
     * 
     * @param array $form_field_layout
     * @param ?int $item_id
     * @return array
     */
    public static function dynamicChangeFormLayout(array $form_field_layout, ?int $item_id): array
    {
        $User = Auth::guard('adm')->user();
        if ($User->is_super_admin) {
            return $form_field_layout;
        }

        return [
            'Details'   => ['title', 'permissions'],
            'sidebar'   => ['active']
        ];
    }

    /**
     * Get permission options - callback used to generate the
     * available options for the multi-select field
     * 
     * @return array
     */
    public static function getPermissionOptions(): array
    {
        return self::generatePermissions();
    }

    /**
     * Generate permission options based on all model configuration
     * classes in the application
     * 
     * @return array
     */
    protected static function generatePermissions(): array
    {
        $permissions = [];

        $config_classes = self::getConfigClasses();
        foreach ($config_classes as $Class) {
            $RefClass = new ReflectionClass($Class);
            if ($RefClass->hasProperty('url_slug') && $RefClass->hasProperty('title')) {
                $permissions[$RefClass->getStaticPropertyValue('url_slug')] = $RefClass->hasProperty('permission_title')
                    ? $RefClass->getStaticPropertyValue('permission_title')
                    : $RefClass->getStaticPropertyValue('title');
            }
            if ($RefClass->hasProperty('permission_slugs')) {
                foreach ($RefClass->getStaticPropertyValue('permission_slugs') as $slug => $title ){
                    $permissions[$slug] = $title;
                }
            }
        }

        $permissions['file-manager'] = 'File manager';

        asort($permissions);
        return $permissions;
    }

    /**
     * Returns all model configuration classes within the application,
     * where each class has a URL slug & title, or a custom set of
     * permissions
     * 
     * @return array
     */
    protected static function getConfigClasses(): array
    {
        $config_classes = ClassFinder::getConfigClasses();
        return array_filter($config_classes, function ($Class) {
            $RefClass = new ReflectionClass($Class);
            $has_url_and_title = $RefClass->hasProperty('url_slug') && $RefClass->hasProperty('title');
            $has_slugs = $RefClass->hasProperty('permission_slugs');
            return $has_url_and_title || $has_slugs;
        });   
    }
}