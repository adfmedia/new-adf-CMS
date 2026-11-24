<?php

namespace Modules\GlobalBlocks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\CachesQueries;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Scopes\LanguageScope;
use Modules\GlobalBlocks\Config\CommonBlocksConfig;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Casts\PageBuilder;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class CommonBlock extends Model
{
    use CachesQueries, SoftDeletes, HasCompositePrimaryKey;

    protected $primaryKey = ['common_block_id', 'language_id', 'version'];
    protected $table = 'common_blocks';

    public $config_class = CommonBlocksConfig::class;

    protected function casts(): array
    {
        return [
            'page_builder' => PageBuilder::class
        ];
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn() => route('adm.route', ['common-blocks', 'edit', $this->common_block_id])
        );
    }
}
