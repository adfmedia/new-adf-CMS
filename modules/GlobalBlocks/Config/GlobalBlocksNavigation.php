<?php

namespace Modules\GlobalBlocks\Config;

use Adm\Interfaces\NavigationInterface;

class GlobalBlocksNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Global Blocks',
                'url'       => route('adm.route', 'global-blocks'),
                'position'  => 60,
                'sub'       => [
                    [
                        'title'     => 'Common Blocks',
                        'url'       => route('adm.route', 'common-blocks'),
                        'position'  => 999
                    ],
                ]
            ]
        ];
    }
}