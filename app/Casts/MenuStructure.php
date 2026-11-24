<?php

namespace App\Casts;

use App\Models\Url;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MenuStructure extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return Collection
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        $links = collect(json_decode($value, true));
        return $this->parseLinks($links);
    }

    /**
     * {@inheritdoc}
     * 
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value);
    }

    /**
     * Parse the stored menu links & set their URLs, targets & rel attributes
     * 
     * @param Collection|array $links
     * @return Collection
     */
    protected function parseLinks(Collection|array $links): Collection|array
    {
        foreach ($links as $idx => $link) {
            data_set($link, 'target', '');
            data_set($link, 'rel', '');

            switch ($link['type']) {
                case 'internal':
                    $Url = Url::find($link['value']);
                    if ($Url && $Url->urlable) {
                        $link_url = route('theme', $Url->url);
                        data_set($link, 'link', $link_url);
                    }
                    break;
                case 'external':
                    data_set($link, 'link', $link['value']);
                    if (!isset($link['new_tab'])) {
                        data_set($link, 'new_tab', true);
                    }
                    data_set($link, 'target', ' target="_blank"');
                    break;
            }

            if (!isset($link['new_tab'])) { data_set($link, 'new_tab', false); }
            if (!isset($link['nofollow'])) { data_set($link, 'nofollow', false); }

            if (isset($link['nofollow']) && $link['nofollow']) {
                data_set($link, 'rel', ' rel="nofollow"');
            }

            if (isset($link['children'])) {
                data_set($link, 'children', $this->parseLinks($link['children']));
            }
            data_set($links, $idx, $link);

            if (empty($link['link'])) {
                unset($links[$idx]);
            }
        }

        return $links;
    }
}