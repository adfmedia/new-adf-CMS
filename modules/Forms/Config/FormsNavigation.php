<?php

namespace Modules\Forms\Config;

use Adm\Interfaces\NavigationInterface;
use Modules\Forms\Models\Form;

class FormsNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        $links = [
            [
                'title'     => 'Forms',
                'url'       => route('adm.route', 'forms'),
                'position'  => 900
            ],
        ];

        $submission_items = [];
        foreach (Form::get() as $idx => $Form) {
            $submission_items[] = [
                'title'     => $Form->title,
                'url'       => route('adm.route', ['form-submissions', $Form->id]),
                'position'  => $idx
            ];
        }
        if (!empty($submission_items)) {
            $links[] = [
                'title'     => 'Form submissions',
                'url'       => '#',
                'position'  => 901,
                'sub'       => $submission_items
            ];
        }

        return $links;
    }
}
