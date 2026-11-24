<?php

namespace Modules\Forms\Blocks;

use App\Blocks\AbstractBlock;
use Modules\Forms\Models\Form as ModelsForm;

class Form extends AbstractBlock
{
    public static $title        = 'Form';
    public static $description  = 'Add a form to the page';
    public static $preview      = [
        'src'       => 'images/block-previews/form.png',
        'alt'       => 'Form block preview',
        'width'     => 1565,
        'height'    => 376,
        'viteable'  => true
    ];

    public static $fields       = [
        'form'  => [
            'label'         => 'Form',
            'help'          => 'Select the form to display',
            'type'          => 'relation',
            'relation'      => [
                'to'            => ModelsForm::class,
                'empty_label'   => 'Select a form',
            ],
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ]
    ];
}