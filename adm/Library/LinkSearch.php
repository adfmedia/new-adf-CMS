<?php

namespace Adm\Library;

use App\Models\File;
use App\Models\Url;
use Illuminate\Database\Eloquent\Collection;

class LinkSearch
{
    /**
     * Search all internal URLs & page titles for the provided term,
     * returning an array of links for use across the CMS
     * 
     * @param string $search_term
     * @return array
     */
    public static function search_links(string $search_term): array
    {
        $base_url = route('theme');
        if (stripos($search_term, $base_url) === 0) {
            $search_term = ltrim(str_replace($base_url, '', $search_term), '/');
        }
        
        $links = [];
        $UrlResults = new Collection();

        // Search links by URL
        $MatchedUrls = Url::where('url', 'LIKE', '%' . $search_term . '%')->get();
        foreach ($MatchedUrls as $MatchedUrl) {
            if (!$MatchedUrl->urlable) { continue; } // If the URL has no linked resource, don't show it

            $links[] = [
                'type'  => 'internal',
                'value' => route('theme', $MatchedUrl->url),
                'label' => $MatchedUrl->urlable->title . ' (/' . $MatchedUrl->url . ')'
            ];
            $UrlResults->push($MatchedUrl);
        }

        // Then, search links by URLable class
        $classes = Url::select('urlable_type')->distinct()
            ->get()
            ->pluck('urlable_type')
            ->toArray();
        foreach ($classes as $class_name) {
            $class_inst = new $class_name();

            $search_field = 'title';
            if (property_exists($class_inst, 'url_search_field')) {
                $search_field = $class_inst->url_search_field;
            }

            $Results = $class_name::query()
                ->where($search_field, 'LIKE', '%' . $search_term . '%')
                ->get();
            foreach ($Results as $Result) {
                if (!$Result->url_record) { continue; } // If the linked resource has no URL, don't show it
                if ($UrlResults->contains($Result->url_record)) { continue; } // If we've already matched the URL, don't show it

                $links[] = [
                    'type'  => 'internal',
                    'value' => route('theme', $Result->url_record->url),
                    'label' => $Result->{$search_field} . ' (/' . $Result->url_record->url . ')'
                ];
            }
        }
        
        // Then, search files
        $MatchedFiles = File::where('title', 'LIKE', '%' . $search_term . '%')->get();
        foreach ($MatchedFiles as $MatchedFile) {
            $links[] = [
                'type'  => 'file',
                'value' => route('file', $MatchedFile->full_path),
                'label' => $MatchedFile->title . ' (File)'
            ];
        }

        return $links;
    }
}