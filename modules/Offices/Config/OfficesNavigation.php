<?php

namespace Modules\Offices\Config;

use Adm\Interfaces\NavigationInterface;

class OfficesNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Offices',
                'url'       => route('adm.route', 'offices'),
                'position'  => 50
            ]
        ];
    }
}