<?php

namespace Modules\TeamMembers\Config;

use Adm\Interfaces\NavigationInterface;

class TeamMembersNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Team members',
                'url'       => route('adm.route', 'team-members'),
                'position'  => 60
            ]
        ];
    }
}