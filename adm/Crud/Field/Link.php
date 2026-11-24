<?php

namespace Adm\Crud\Field;

use App\Helpers\ClassFinder;
use App\Models\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Link extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $sortable = false;

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        // Do nothing
    }

    /**
     * Returns a JSON response of grouped links for use within
     * the link picker modal
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLinksForModal(): JsonResponse
    {
        $link_groups = [];
        foreach ($this->getGroupedIndexableUrls() as $group_title => $links) {
            $link_groups[] = [
                'title' => $group_title,
                'links' => array_values(Arr::sort($links, function (array $link) {
                    return $link['label'];
                }))
            ];
        }

        return response()->json([
            'link_groups'   => $link_groups
        ]);
    }

    /**
     * Returns a JSON response of grouped links for use within
     * the TinyMCE link picker
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTinymceLinkList(): JsonResponse
    {
        $links = [];
        foreach ($this->getGroupedIndexableUrls() as $group_title => $group_links) {
            $links[] = [
                'title' => $group_title,
                'menu'  => array_values(Arr::sort($group_links, function (array $link) {
                    return $link['label'];
                }))
            ];
        }
        return response()->json(['links' => $links]);
    }

    /**
     * Returns and caches a grouped list of indexable URLs for
     * use within getLinksForModal() and getTimemceLinkList()
     * 
     * @return array
     */
    protected function getGroupedIndexableUrls(): array
    {
        return Cache::remember(
            'cms.grouped_link_list',
            config('database.cache_seconds'),
            function () {
                $model_title_map    = [];
                $class_links        = [];

                foreach (Url::get() as $Url) {
                    if (!$Url->urlable) { continue; }

                    $model_class = $Url->urlable_type;
                    if (!isset($model_title_map[$model_class])) {
                        $ConfigReflection = ClassFinder::findConfigClassForModel($model_class);
                        if (!$ConfigReflection) { continue; }

                        $model_title_map[$model_class] = $ConfigReflection->getStaticPropertyValue('title');
                    }
                    $model_group = $model_title_map[$model_class];

                    if (!isset($class_links[$model_group])) {
                        $class_links[$model_group] = [];
                    }

                    $Urlable = $Url->urlable;
                    $class_links[$model_group][] = [
                        'value' => $Urlable->url,
                        'slug'  => '/' . ltrim($Url->url, '/'),
                        'label' => $Urlable->title,
                        'title' => $Urlable->title
                    ];
                }

                uksort($class_links, function ($a, $b) {
                    if ($a == 'Pages') { return -1; }
                    if ($b == 'Pages') { return 1; }
                    return strcasecmp($a, $b);
                });

                return $class_links;
            }
        );
    }
}