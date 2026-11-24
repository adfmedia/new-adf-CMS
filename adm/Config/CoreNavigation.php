<?php

namespace Adm\Config;

use Adm\Interfaces\NavigationInterface;

class CoreNavigation implements NavigationInterface
{
    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Dashboard', 
                'url'       => route('adm.route'), 
                'position'  => 0
            ],

            [
                'title'     => 'Menus',
                'url'       => route('adm.route', 'menus'),
                'position'  => 998
            ],
            [
                'title'     => 'File manager',
                'url'       => route('adm.file-manager'),
                'position'  => 999
            ],
            [
                'title'     => 'Settings', 
                'url'       => '#',
                'position'  => 1000,
                'sub'       => [
                    [
                        'title'     => 'Common text',
                        'url'       => route('adm.route', 'common-text'),
                        'position'  => 0
                    ],
                    [
                        'title'     => 'Social media',
                        'url'       => route('adm.route', 'social-media'),
                        'position'  => 5
                    ],
                    [
                        'title'     => 'Redirects',
                        'url'       => route('adm.route', 'redirects'),
                        'position'  => 90
                    ],
                    [
                        'title'     => 'Modules',
                        'url'       => route('adm.route', 'modules'),
                        'position'  => 95
                    ],
                    [
                        'title'     => 'Admin users',
                        'url'       => route('adm.route', 'admin-users'),
                        'position'  => 100,
                    ],
                    [
                        'title'     => 'Languages',
                        'url'       => route('adm.route', 'languages'),
                        'position'  => 9999
                    ]
                ]
            ]
        ];
    }
}