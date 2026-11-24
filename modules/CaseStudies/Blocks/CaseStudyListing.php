<?php

namespace Modules\CaseStudies\Blocks;

use App\Blocks\AbstractBlock;
use App\Models\PageBuilderBlock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\CaseStudies\Models\CaseStudy;

class CaseStudyListing extends AbstractBlock
{
    public static $title        = 'Case study listing';
    public static $description  = 'Contains a listing of case studies';
    public static $preview      = [
        'src'       => 'images/block-previews/case-study-listing.png',
        'alt'       => 'Case study listing block preview',
        'width'     => 1599,
        'height'    => 596,
        'viteable'  => true
    ];

    public static $fields       = [
        'heading'   => [
            'label'         => 'Heading',
            'help'          => 'Optionally add a heading to display above the case studies',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'content'   => [
            'label'         => 'Content',
            'help'          => 'Optionally add content, displayed above the case studies',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'case_studies'  => [
            'label'             => 'Case studies',
            'help'              => 'Select specific case studies to display, or leave empty to display the latest 3',
            'type'              => 'orderable_select',
            'relation'          => [
                'to'                => CaseStudy::class
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
            !isset($content['case_studies']) || 
            empty($content['case_studies']) ||
            !($content['case_studies'] instanceof Collection)
        ) {
            $content['case_studies'] = CaseStudy::orderBy('date', 'desc')->limit(3)->get();
        }

        return $content;
    }
}
