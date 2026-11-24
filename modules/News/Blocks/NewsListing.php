<?php

namespace Modules\News\Blocks;

use App\Blocks\AbstractBlock;
use App\Models\PageBuilderBlock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\News\Models\NewsArticle;

class NewsListing extends AbstractBlock
{
    public static $title        = 'News listing';
    public static $description  = 'Contains a listing of news articles';
    public static $preview      = [
        'src'       => 'previews/news-listing.png',
        'alt'       => 'News listing block preview',
        'width'     => 1571,
        'height'    => 678,
        'module_asset' => true
    ];

    public static $fields       = [
        'heading'   => [
            'label'         => 'Heading',
            'help'          => 'Optionally add a heading to display above the articles',
            'type'          => 'string',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'content'   => [
            'label'         => 'Content',
            'help'          => 'Optionally add content, displayed above the articles',
            'type'          => 'wysiwyg',
            'validation'    => [
                'rules'         => [],
                'messages'      => []
            ]
        ],
        'articles'  => [
            'label'             => 'Articles',
            'help'              => 'Select specific articles to display, or leave empty to display the latest 3',
            'type'              => 'orderable_select',
            'relation'          => [
                'to'                => NewsArticle::class
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
            !isset($content['articles']) ||
            empty($content['articles']) ||
            !($content['articles'] instanceof Collection)
        ) {
            $content['articles'] = NewsArticle::orderBy('article_date', 'desc')->limit(3)->get();
        }
        
        return $content;
    }
}