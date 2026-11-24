<?php

namespace Modules\Testimonials\Blocks;

use App\Blocks\AbstractBlock;
use App\Models\PageBuilderBlock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Testimonials\Models\Testimonial;

class Testimonials extends AbstractBlock
{
    public static $title        = 'Testimonials';
    public static $description  = 'Add a scrolling carousel of testimonials';
    public static $preview      = [
        'src'       => 'images/block-previews/testimonials.png',
        'alt'       => 'Testimonials block preview',
        'width'     => 1573,
        'height'    => 526,
        'viteable'  => true
    ];

    public static $fields       = [
        'heading'   => [
            'label'         => 'Heading',
            'help'          => 'Optionally add a heading to display above the testionials',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'content'   => [
            'label'         => 'Content',
            'help'          => 'Optionally add content, displayed above the testionials',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'testimonials'  => [
            'label'             => 'Testimonials',
            'help'              => 'Select specific testimonials to display, or leave empty to display the latest 3',
            'type'              => 'orderable_select',
            'relation'          => [
                'to'                => Testimonial::class
            ],
            'validation'        => [
                'rules'             => [],
                'messages'          => []
            ]
        ]
    ];

    public static function processContents(array $content, Model $model, PageBuilderBlock $Block): array
    {
        if (
            !isset($content['testimonials']) ||
            empty($content['testimonials']) ||
            !($content['testimonials'] instanceof Collection)
        ) {
            $content['testimonials'] = Testimonial::orderBy('date', 'desc')->limit(3)->get();
        }

        return $content;
    }
}