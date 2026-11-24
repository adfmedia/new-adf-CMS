<?php

namespace Modules\GlobalBlocks\Blocks;

use App\Blocks\AbstractBlock;
use Modules\GlobalBlocks\Models\CommonBlock as CommonBlockModel;
use Illuminate\Database\Eloquent\Model;
use App\Models\PageBuilderBlock;
use Illuminate\Support\Collection;

class CommonBlock extends AbstractBlock
{
    public static $title       = 'Common Block';
    public static $description = 'Add a preset common block to the page';
    public static $enable_global_fields = false;
    public static $preview     = [
        'src'       => 'previews/common-block.png',
        'alt'       => 'Common block block preview',
        'width'     => 1889,
        'height'    => 503,
        'module_asset' => true,
    ];

    public static $fields = [
        'common_block' => [
            'label'         => 'Common Block',
            'help'          => 'Select a common block to display on the page',
            'type'          => 'relation',
            'relation'      => [
                'to'             => CommonBlockModel::class,
                'order_by_field' => 'title',
            ]
        ]
    ];

     /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public static function processContents(array $content, Model $model, PageBuilderBlock $Block): array
    {
        $CommonBlocks = new Collection();

        if(isset($content['common_block']) && !empty($content['common_block'])) {
            $CommonBlock = $content['common_block'];
            $CommonBlocks = $CommonBlock->page_builder;
        }

        $content['CommonBlocks'] = $CommonBlocks;
        return $content;
    }
}