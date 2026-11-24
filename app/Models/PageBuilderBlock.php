<?php

namespace App\Models;

use App\Blocks\AbstractBlock;
use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use App\Models\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use ReflectionClass;
use Modules\GlobalBlocks\Models\CommonBlock;

#[ScopedBy([LanguageScope::class, StatusEnabledScope::class])]
class PageBuilderBlock extends Model
{
    use SoftDeletes, CachesQueries;

    public $table       = 'page_builder_blocks';
    public $primaryKey  = 'id';

    protected $fillable = [
        'morphable_id',
        'morphable_type',
        'morphable_field',
        'language_id',
        'version',
        'block_class',
        'sort_order',
        'status',
    ];

    /**
     * Returns the block's content records
     * 
     * @return HasMany
     */
    public function contents(): HasMany
    {
        return $this->hasMany(PageBuilderBlockContent::class);
    }

    /**
     * Return the common block settings for the current
     * block, or null if not found
     * 
     * @return CommonBlock|null
     */
    public function commonBlock(): ?CommonBlock
    {
        $FieldContent = $this->contents()->where('field', 'common_block')->first();

        return $FieldContent
            ? CommonBlock::where('common_block_id', $FieldContent->value)->first()
            : null;
    }

    /**
     * Return the blade file for the block, either from a property
     * assigned to the block class, or automatically based on
     * the block name
     * 
     * @return Attribute
     */
    protected function bladeFile(): Attribute
    {
        $Block = $this;
        return Attribute::make(
            get: function (mixed $value) use ($Block) {
                $Ref = $Block->getBlockReflectionClass();
                if ($Ref->hasProperty('blade_file')) {
                    return $Ref->getStaticPropertyValue('blade_file');
                }

                $class_parts = explode('\\', $Ref->getName());
                $class_name = end($class_parts);
                return Str::snake($class_name);
            }
        )->shouldCache();
    }

    /**
     * Returns an instance of the block configuration class for this
     * block
     * 
     * @return AbstractBlock
     */
    public function getBlockClassInstance(): AbstractBlock
    {
        return $this->getBlockReflectionClass()->newInstance();
    }

    /**
     * Returns the reflection class for this block's configuration class
     * 
     * @return ReflectionClass
     */
    public function getBlockReflectionClass(): ReflectionClass
    {
        return new ReflectionClass($this->block_class);
    }

    /**
     * Local boolean determining whether a block has
     * already added a preload (i.e. for LCP image)
     * 
     * @var bool
     */
    protected static $PRELOAD_TRIGGERED = false;

    /**
     * Return a boolean flag determining whether the
     * current block should preload images
     * 
     * @return Attribute
     */
    protected function shouldPreload(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!self::$PRELOAD_TRIGGERED) {
                    self::$PRELOAD_TRIGGERED = true;
                    return true;
                }
                
                return false;
            }
        );
    }

    /**
     * Toggle the preload trigger to true, preventing
     * any blocks from adding this without cause.
     * 
     * For example, if we have a fixed image banner
     * on page, we can prevent any blocks from preloading
     * images that aren't needed.
     * 
     * @return void
     */
    public static function triggerPreloaded(): void
    {
        self::$PRELOAD_TRIGGERED = true;
    }
}