<?php

namespace Modules\Pages\Config;

use Adm\Interfaces\NavigationInterface;

class PagesNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Pages',
                'url'       => route('adm.route', 'pages'),
                'position'  => 10
            ],
        ];
    }
}