<?php

namespace Modules\TeamMembers\App\Controllers;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use Modules\Pages\Models\Page;
use Modules\TeamMembers\Models\TeamMember;

class TeamMemberController extends Controller
{
    public function index(Page $Page)
    {
        return view(
            'team-members.listing',
            [
                'Page'          => $Page,
                'TeamMembers'   => TeamMember::priorityOrder()->get(),
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url),
            ]
        );
    }

    public function team_member(TeamMember $TeamMember)
    {
        return view(
            'team-members.team-member',
            [
                'Page'          => $TeamMember,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($TeamMember->url)
            ]
        );
    }
}