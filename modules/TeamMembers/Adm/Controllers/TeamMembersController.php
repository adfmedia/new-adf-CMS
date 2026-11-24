<?php

namespace Modules\TeamMembers\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\TeamMembers\Config\TeamMembersConfig;

class TeamMembersController extends AdmController
{
    public function team_members($method, $id)
    {
        return $this->Crud->boot($method, $id, TeamMembersConfig::class);
    }
}