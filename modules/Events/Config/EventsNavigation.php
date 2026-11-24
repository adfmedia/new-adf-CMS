<?php

namespace Modules\Events\Config;

use Adm\Interfaces\NavigationInterface;

class EventsNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Events',
                'url'       => route('adm.route', 'events'),
                'position'  => 40,
                'include_in_sub'    => true,
                'sub'       => [
                    [
                        'title'     => 'Categories',
                        'url'       => route('adm.route', 'event-categories'),
                        'position'  => 0
                    ]
                ]
            ]
        ];
    }
}