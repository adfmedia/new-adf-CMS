<?php

namespace Adm\Library;

use App\Helpers\ClassFinder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class SidebarNavigation
{
    /**
     * Builds an array of navigation links for use within
     * the CMS sidebar
     * 
     * @return array
     */
    public static function build(): array
    {
        $classes = ClassFinder::getAdminNavigationClasses();
        $navigation = self::generateNavigationArray($classes);
        $navigation = self::filterNavigationBasedOnPermissions($navigation);
        $navigation = self::addActiveFlagToNavigationLinks($navigation);
        return $navigation;
    }

    /**
     * Generates the base array for navigation links based
     * on the provided navigation classes
     * 
     * @param array $nav_classes
     * @return array
     */
    protected static function generateNavigationArray(array $nav_classes) : array
    {
        $navigation = [];

        foreach ($nav_classes as $NavClassInst) {
            $class_nav = $NavClassInst->getNavItems();
            foreach ($class_nav as $nav) {
                $navigation[] = $nav;
            }
        }

        $navigation = Arr::sort($navigation, function (array $value) {
            return $value['position'];
        });

        return $navigation;
    }

    /**
     * Filters the pre-generated navigation links based
     * on what the current CMS user has permission to
     * access
     * 
     * @param array $navigation
     * @return array
     */
    protected static function filterNavigationBasedOnPermissions(array $navigation) : array
    {
        /** @var \Adm\Models\AdmUser */
        $User = Auth::guard('adm')->user();
        foreach ($navigation as $idx => $links) {
            if (isset($links['sub'])) {
                $navigation[$idx]['sub'] = self::filterNavigationBasedOnPermissions($links['sub']);
                if (empty($navigation[$idx]['sub'])) {
                    unset($navigation[$idx]['sub']);
                }
            }

            $area = ltrim(str_replace(route('adm.route'), '', $links['url']), '/');
            $area_parts = explode('/', $area);
            $area = $area_parts[0];

            // Don't disable dashboard or hashed links
            if (empty($area) || $area == '#') {
                // Unless they're supposed to have dropdown items that have
                // been filtered out
                if (isset($links['sub']) && !isset($navigation[$idx]['sub'])) {
                    unset($navigation[$idx]);
                }
                continue;
            }
            if (!$User->canAccess($area)) {
                unset($navigation[$idx]);
            }
        }

        return $navigation;
    }

    /**
     * Adds active & expanded flags to sidebar navigation
     * links & groups based on the current URL
     * 
     * @param array $navigation
     * @return array
     */
    protected static function addActiveFlagToNavigationLinks(array $navigation) : array
    {
        $Route = request()->route();
        $parameters = $Route->parameters;
        if (!isset($parameters['section'])) { return $navigation; }

        foreach ($navigation as $idx => $link) {
            if (self::isLinkActive($link['url'], $parameters)) {
                $navigation[$idx]['active'] = true;
                $navigation[$idx]['expanded'] = true;
            }

            if (isset($link['sub']) && !empty($link['sub'])) {
                foreach ($link['sub'] as $sub_idx => $sub_link) {
                    if (self::isLinkActive($sub_link['url'], $parameters)) {
                        $navigation[$idx]['sub'][$sub_idx]['active'] = true;
                        $navigation[$idx]['expanded'] = true;
                    }
                }
            }
        }

        return $navigation;
    }

    /**
     * Determines whether the provided link is active based
     * on the provided parameters
     * 
     * @param string $link_url
     * @param array $parameters
     * @return bool
     */
    protected static function isLinkActive(string $link_url, array $parameters): bool
    {
        $current_base_link = route('adm.route', $parameters['section']);
        if ($link_url == $current_base_link) {
            return true;
        } elseif (stripos($link_url, $current_base_link) === 0) {
            $full_link = route('adm.route', $parameters);
            if ($link_url == $full_link) {
                return true;
            } elseif (isset($parameters['method'])) {
                $method_link = route('adm.route', [$parameters['section'], $parameters['method']]);
                if ($link_url == $method_link) {
                    return true;
                }
            }
        }

        return false;
    }
}