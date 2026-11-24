<?php

namespace Adm\Crud\Field;

use Adm\Crud\CrudField;
use App\Helpers\ClassFinder;
use App\Models\PageBuilderBlock;
use App\Models\PageBuilderBlockContent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Vite;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Str;
use Modules\GlobalBlocks\Blocks\CommonBlock;

class PageBuilder extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $sortable = false;

    /**
     * {@inheritdoc}
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
        $Blocks = data_get($Item, $field, []);

        foreach ($Blocks as $Block) {
            $BlockClass = $Block->getBlockReflectionClass();
            $ClassInst = $Block->getBlockClassInstance();
            data_set($Block, 'render_settings', $ClassInst->getRenderSettings());
            data_set($Block, 'form_fields', $this->processBlockFieldDetails($BlockClass->getStaticPropertyValue('fields')));

            $content = $Block->content;
            foreach (data_get($Block, 'form_fields') as $sub_field => $sub_details) {
                $Field = new CrudField($this->CrudInterface, $sub_details['type']);
                $Field->prepareForForm($content, $sub_field, $sub_details);
            }
            data_set($Block, 'content', $content);
        }

        data_set($Item, $field, $Blocks);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        // Do nothing as field shouldn't exist on item
    }

    /**
     * {@inheritdoc}
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        if (empty($value)) { return; }
        
        $ConfigClass = $this->CrudInterface->getConfigClass();

        // Run afterStored (as using format of $_POST array)
        foreach ($value as $sort_order => $block) {
            $BlockClass = new ReflectionClass($block['class']);
            $block_fields = $BlockClass->getStaticPropertyValue('fields');
            foreach ($block_fields as $block_field => $block_field_details) {
                $block_field_value = isset($block['content'][$block_field]) ? $block['content'][$block_field] : null;

                $Field = new CrudField($this->CrudInterface, $block_field_details['type']);
                $Field->afterStored(
                    $Item,
                    $field . '.' . $sort_order . '.content.' . $block_field,
                    isset($block['content'][$block_field]) ? $block['content'][$block_field] : null,
                    $block_field_details
                );
            }
        }

        // Run castables to format values for storage
        $value = collect($value);
        $Castable = new \App\Casts\PageBuilder();
        $Castable->set($Item, $field, $value, $Item->getAttributes());

        // Save blocks
        foreach ($value as $sort_order => $block) {
            $PBB = PageBuilderBlock::create([
                'morphable_id'      => data_get($Item, $this->CrudInterface->getClassIDField()),
                'morphable_type'    => $ConfigClass->getStaticPropertyValue('class'),
                'morphable_field'   => $field,
                'language_id'       => data_get($Item, 'language_id'),
                'version'           => data_get($Item, 'version', 1),
                'block_class'       => $block['class'],
                'sort_order'        => $sort_order,
                'status'            => $block['status'] ? true : false
            ]);

            foreach ($block['content'] as $content_field => $content_value) {
                PageBuilderBlockContent::create([
                    'page_builder_block_id' => $PBB->id,
                    'field'                 => $content_field,
                    'value'                 => $content_value
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterDeleted(Model $Item, string $field, array $details): void
    {
        $ConfigClass = $this->CrudInterface->getConfigClass();
        $Blocks = PageBuilderBlock::where([
            'morphable_id'      => data_get($Item, $this->CrudInterface->getClassIDField()),
            'morphable_type'    => $ConfigClass->getStaticPropertyValue('class'),
            'morphable_field'   => $field,
            'language_id'       => data_get($Item, 'language_id'),
        ])->get();
        foreach ($Blocks as $Block) {
            $Block->contents()->delete();
            $Block->delete();
        }
    }

    /**
     * Returns a JSON response containing all available blocks
     * for the current page type
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadBlockListing(): JsonResponse
    {
        $filter = request()->input('filter');
        $settings = request()->input('settings');
        $settings = Str::isJson($settings) ? json_decode($settings, true) : [];
        
        $AllClasses = ClassFinder::getPageBuilderBlockClasses();
        if ($filter) {
            // Filter the collection...
            $AllClasses = array_filter($AllClasses, function ($BlockClass) use ($filter) {
                if ($BlockClass->hasProperty('block_filter')) {
                    $block_filter = $BlockClass->getStaticPropertyValue('block_filter');
                    return $block_filter == $filter;
                }
                return false;
            });
        } else {
            // Filter the collection to remove filtered blocks
            $AllClasses = array_filter($AllClasses, function ($BlockClass) {
                if (!$BlockClass->hasProperty('block_filter')) {
                    return true;
                }
                $block_filter = $BlockClass->getStaticPropertyValue('block_filter');
                return $block_filter === false;
            });
        }

        $blocks = [];
        foreach ($AllClasses as $BlockClass) {
            if(data_get($settings, 'hide_common_block') 
                && $BlockClass->getName() == CommonBlock::class) {
                continue;
            }
            $preview_image = $BlockClass->getStaticPropertyValue('preview');
            if (empty($preview_image) || !is_array($preview_image) || empty($preview_image['src'])) {
                $preview_image = false;
            } else {
                if (isset($preview_image['viteable']) && $preview_image['viteable']) {
                    try {
                        $preview_image['src'] = Vite::asset('resources/' . $preview_image['src']);
                    } catch (Exception $e) {}
                }
            }

            $blocks[] = [
                'class'         => $BlockClass->getName(),
                'title'         => $BlockClass->getStaticPropertyValue('title'),
                'description'   => $BlockClass->getStaticPropertyValue('description'),
                'preview'       => $preview_image
            ];
        }

        usort($blocks, function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });

        return response()->json(['blocks' => $blocks]);
    }

    /**
     * Returns a View representation of a new block
     * 
     * @return \Illuminate\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function renderBlock(): Response
    {
        $item_class = request()->input('item_class');
        $Item = new $item_class();

        $class = request()->input('class');
        try {
            $BlockClass = new ReflectionClass($class);
        } catch (Exception $e) {
            throw new NotFoundHttpException();
        }

        $BlockClassInst = $BlockClass->newInstance();
        $this_field = request()->input('field_name') . '[' . request()->input('idx') . ']';
        return response()->view(
            request()->input('visual_editor') ? 'crud.blocks.visual-editor-wrapper' : 'crud.blocks.wrapper',
            [
                'Item'          => $Item,
                'is_new'        => true,
                'field_name'    => $this_field,
                'field_prefix'  => $this_field . '[content]',
                'sort_order'    => request()->input('idx'),
                'status'        => true,
                'block'         => $BlockClassInst->getRenderSettings(),
                'fields'        => $this->processBlockFieldDetails($BlockClass->getStaticPropertyValue('fields'))
            ]
        );
    }

    /**
     * For each field within a block, perform any additional
     * field modification as required
     * 
     * @param array $fields
     * @return array
     */
    protected function processBlockFieldDetails(array $fields): array
    {
        foreach ($fields as $field => $details) {
            $Field = new CrudField($this->CrudInterface, $details['type']);
            $details['required'] = $this->isFieldRequired($details);
            $fields[$field] = $Field->modifyFieldDetailsForForm($details, null);
        }
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        // Do nothing
    }
}