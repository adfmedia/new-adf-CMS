<?php

namespace Adm\Crud\Field;

use Adm\Library\BingWebmaster\IndexNow as BingIndexNow;
use Adm\Library\Database\TableHelper;
use App\Helpers\RouteHelper;
use App\Models\Redirect;
use App\Models\Scopes\LanguageScope;
use App\Models\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Modules\Search\Models\SearchIndex;

class Path extends Field
{
    /**
     * The default meta title maximum length
     * 
     * @var int
     */
    const DEFAULT_META_TITLE_MAX        = 255;

    /**
     * The default meta description maximum length
     * 
     * @var int
     */
    const DEFAULT_META_DESCRIPTION_MAX  = 320;

    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $UrlRecord = $this->getItemUrl($Item);
        if ($UrlRecord) {
            data_set($Item, $field, $UrlRecord->url);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
        $url_details = [
            'url'               => null,
            'meta_title'        => null,
            'meta_description'  => null,
            'no_index'          => false,
            'system_page'       => null,
            'route'             => null
        ];
        $UrlRecord = $this->getItemUrl($Item);
        if ($UrlRecord) {
            $url_details = [
                'url'               => ltrim($UrlRecord->url, '/'),
                'meta_title'        => $UrlRecord->meta_title,
                'meta_description'  => $UrlRecord->meta_description,
                'no_index'          => $UrlRecord->no_index,
                'system_page'       => $UrlRecord->system_page,
                'route'             => $UrlRecord->route
            ];
        }

        $details = $this->modifyFieldDetailsForForm($details, $Item);
        if (!empty($details['prefix'])) {
            if (!empty($details['prefix']['prefix_class'])) {
                $url_details['prefix_path'] = $this->getRelatedPrefixUrl(
                    $details['prefix']['prefix_class'],
                    $details['prefix']['relation'],
                    $Item
                );
            }

            if (
                (!isset($url_details['prefix_path']) || empty($url_details['prefix_path'])) 
                && !empty($details['prefix']['base_prefix'])
            ) {
                $url_details['prefix_path'] = $details['prefix']['base_prefix'];
            }

            if (
                !empty($details['prefix']['base_prefix']) ||
                !empty($details['prefix']['prefix_class'])
            ) {
                $url_parts = explode('/', $url_details['url']);
                if (isset($details['allow_slashes']) && $details['allow_slashes']) {
                    if (isset($url_details['prefix_path']) && !empty($url_details['prefix_path'])) {
                        $prefix_parts = explode('/', $url_details['prefix_path']);
                        foreach ($prefix_parts as $idx => $part) {
                            if (isset($url_parts[$idx]) && $url_parts[$idx] == $part) {
                                unset($url_parts[$idx]);
                            }
                        }
                        $url_details['url'] = implode('/', $url_parts);
                    }
                } else {
                    $url_details['url'] = array_pop($url_parts);
                }
            }
        }

        data_set($Item, $field, $url_details);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        // Do nothing as we don't want to directly assign the URL 
        // and settings to the item
    }

    /**
     * {@inheritdoc}
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        $ConfigClass = $this->CrudInterface->getConfigClass();

        $url_to_store = isset($value['url']) && !empty($value['url']) ? $value['url'] : '/';
        if (isset($value['prefix']) && !empty($value['prefix'])) {
            $url_to_store = $value['prefix'] . '/' . $url_to_store;
        }

        $url_to_store = trim($this->CrudInterface->getLanguage()->url_slug . '/' . $url_to_store, '/');

        $UrlRecord = $this->getItemUrl($Item);
        if (!$UrlRecord) {
            $urlable_id = data_get($Item, $this->CrudInterface->getClassIDField());
            $language_id = data_get($Item, 'language_id');
            if (RouteHelper::isPreviewRoute()) {
                if (!$urlable_id) { $urlable_id = 0; }
                if (!$language_id) { $language_id = $this->CrudInterface->getLanguageId(); }
            }

            $UrlRecord = Url::insert([
                'url'               => $url_to_store,
                'urlable_id'        => $urlable_id,
                'urlable_type'      => $ConfigClass->getStaticPropertyValue('class'),
                'language_id'       => $language_id,
                'meta_title'        => isset($value['meta_title']) ? $this->dbFormatMetaTitle($value['meta_title']) : null,
                'meta_description'  => isset($value['meta_description']) ? $this->dbFormatMetaDescription($value['meta_description']) : null,
                'no_index'          => isset($value['no_index']) ? $value['no_index'] : false,
                'system_page'       => isset($value['system_page']) ? $value['system_page'] : false,
                'route'             => isset($value['route']) ? $value['route'] : null
            ]);
        } else {
            $needs_redirect = $url_to_store != $UrlRecord->url;
            $old_url = $UrlRecord->url;

            $UrlRecord->url = $url_to_store;
            $UrlRecord->meta_title = isset($value['meta_title']) ? $this->dbFormatMetaTitle($value['meta_title']) : null;
            $UrlRecord->meta_description = isset($value['meta_description']) ? $this->dbFormatMetaDescription($value['meta_description']) : null;
            $UrlRecord->no_index = isset($value['no_index']) ? $value['no_index'] : false;
            $UrlRecord->system_page = isset($value['system_page']) ? $value['system_page'] : false;
            $UrlRecord->route = isset($value['route']) ? $value['route'] : null;
            $UrlRecord->save();

            if ($needs_redirect) {
                Redirect::create([
                    'old_url'       => $old_url,
                    'new_url'       => $url_to_store,
                    'redirect_type' => 301,
                    'status'        => true
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function runAfterStoredInPreviewHandler(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function postAfterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        $url_to_store = isset($value['url']) && !empty($value['url']) ? $value['url'] : '/';
        if (isset($value['prefix']) && !empty($value['prefix'])) {
            $url_to_store = $value['prefix'] . '/' . $url_to_store;
        }

        $this->CrudInterface->reindexSearch($url_to_store);

        if (!empty($Item->url)) {
            BingIndexNow::submitUrl($Item->url);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterDeleted(Model $Item, string $field, array $details): void
    {
        $ConfigClass = $this->CrudInterface->getConfigClass();
        $Url = Url::where([
            'urlable_id'    => data_get($Item, $this->CrudInterface->getClassIDField()),
            'urlable_type'  => $ConfigClass->getStaticPropertyValue('class'),
            'language_id'   => data_get($Item, 'language_id'),
        ])->first();
        if ($Url) {
            $this->CrudInterface->delistFromSearch($Url);
            $Url->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForListing(array $details): array
    {
        $details['base_url'] = $this->CrudInterface->getLanguage()->base_url;

        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForForm(array $details, ?Model $Item): array
    {
        if (isset($details['prefix'])) {
            $details['prefix'] = $this->getPrefixDetails($details);
        }

        $details['base_url'] = $this->CrudInterface->getLanguage()->base_url;

        return $details;
    }

    /**
     * Returns details of the path prefix assigned to the field
     * 
     * @param array $field_details
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getPrefixDetails(array $field_details): array
    {
        $prefix_config = $field_details['prefix'];
        if (isset($prefix_config['system_page'])) {
            $SysPg = Url::getSystemPage($prefix_config['system_page']['controller'], $prefix_config['system_page']['method']);
            if ($SysPg && $SysPg->url_record) {
                $prefix_config['base_prefix'] = $SysPg->url_record->url;
            }
        }
        elseif (isset($prefix_config['relation'])) {
            $Config = $this->CrudInterface->getConfigClass();
            $fields = $Config->getStaticPropertyValue('fields');
            
            if (!isset($fields[$prefix_config['relation']])) {
                throw new InvalidArgumentException('Assigned path prefix relational field (' . $prefix_config['relation'] . ') not found in config file.');
            }

            $prefix_field_name = $prefix_config['relation'];
            $prefix_field = $fields[$prefix_field_name];
            if ($prefix_field['type'] != 'relation' || !isset($prefix_field['relation'])) {
                throw new InvalidArgumentException('Assigned path prefix relational field (' . $prefix_field_name . ') is not relational, or does not have a \'to\' class assigned.');
            }
            $RelatedClass = $prefix_field['relation']['to'];
            if (!in_array('App\Models\Traits\HasUrl', class_uses($RelatedClass))) {
                throw new InvalidArgumentException('The related class ' . $RelatedClass . ' does not implement the HasUrl trait.');
            }

            // Check if there's a base prefix as well as the regular relation
            if (isset($field_details['base_prefix'])) {
                if (!isset($field_details['base_prefix']['system_page'])) {
                    throw new InvalidArgumentException('The base prefix configuration for relational prefix ' . $prefix_field_name . ' is missing a system page declaration.');
                }
                $base_syspg_details = $field_details['base_prefix']['system_page'];
                if (!isset($base_syspg_details['controller']) || !isset($base_syspg_details['method'])) {
                    throw new InvalidArgumentException('The base prefix system page configuration for relational prefix ' . $prefix_field_name . ' is missing a controller or method.');
                }

                $SysPg = Url::getSystemPage($base_syspg_details['controller'], $base_syspg_details['method']);
                if ($SysPg && $SysPg->url_record) {
                    $prefix_config['base_prefix'] = $SysPg->url_record->url;
                }
            }

            $prefix_config['prefix_class'] = $RelatedClass;
        }

        return $prefix_config;
    }

    /**
     * Returns a prefix URL for a related item
     * 
     * @param string $class_name                        The class name of the related item
     * @param string $id_field                          The 'primary' ID field for the related item
     * @param \Illuminate\Database\Eloquent\Model $Item The related item
     * @return string
     */
    protected function getRelatedPrefixUrl(string $class_name, string $id_field, Model $Item): string
    {
        return $this->getUrlForClassNameIdAndLanguage(
            $class_name, 
            data_get($Item, $id_field),
            data_get($Item, 'language_id')
        );
    }

    /**
     * Returns the URL record for the provided item, or null
     * if not found
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return ?\App\Models\Url
     */
    protected function getItemUrl(Model $Item): ?Url
    {
        return $this->getUrlRecordForClassNameIdAndLanguage(
            get_class($Item),
            data_get($Item, $this->CrudInterface->getClassIDField()),
            data_get($Item, 'language_id')
        );
    }

    /**
     * Return the URL record for the item matching the provided
     * details
     * 
     * @param string $class_name
     * @param ?int $class_id
     * @param ?int $language_id
     * @return ?\App\Models\Url
     */
    protected function getUrlRecordForClassNameIdAndLanguage(string $class_name, ?int $class_id, ?int $language_id): ?Url
    {
        if ($this->CrudInterface->getMethod() == 'copy') {
            return null;
        }
        
        return Url::withoutGlobalScope(LanguageScope::class)
            ->where([
                'urlable_type'  => $class_name,
                'urlable_id'    => $class_id,
                'language_id'   => $language_id
            ])->first();
    }

    /**
     * Return the URL path for the item matching the provided
     * details
     * 
     * @param string $class_name
     * @param ?int $class_id
     * @param ?int $language_id
     * @return string
     */
    protected function getUrlForClassNameIdAndLanguage(string $class_name, ?int $class_id, ?int $language_id): string
    {
        $UrlForClass = $this->getUrlRecordForClassNameIdAndLanguage($class_name, $class_id, $language_id);
        return $UrlForClass ? $UrlForClass->url : '';
    }

    /**
     * Returns a JSON response containing the prefix URL
     * for the provided item
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPathPrefixForForm(): JsonResponse
    {
        $class_name = request()->input('prefix_class');
        $class_id_value = request()->input('prefix_id');

        if (!$class_name || !$class_id_value) {
            return response()->json([
                'prefix'    => ''
            ]);
        }

        $prefix = $this->getUrlForClassNameIdAndLanguage($class_name, $class_id_value, $this->CrudInterface->getLanguageId());
        if (request()->input('base_prefix')) {
            $base = request()->input('base_prefix');
            if (stripos($prefix, $base) === 0) {
                $prefix = substr($prefix, strlen($base));
            }
        }
        return response()->json([
            'prefix'    => $prefix
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        $Config = $this->CrudInterface->getConfigClass();
        $urlable_ids = Url::where('urlable_type', $Config->getStaticPropertyValue('class'))
            ->where('url', 'LIKE', '%' . $search_term . '%')
            ->select('urlable_id')->get()->pluck('urlable_id')->toArray();
        $query->orWhereIn($this->CrudInterface->getClassIDField(), $urlable_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(Builder $query, string $table, string $field_name, string $sort_dir, array $field_details): void
    {
        $Config = $this->CrudInterface->getConfigClass();
        $query
            ->join('urls', 'urls.urlable_id', '=', $this->CrudInterface->getClassIDField())
            ->where('urls.urlable_type', $Config->getStaticPropertyValue('class'))
            ->orderBy('urls.url', $sort_dir);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAfter(Model $Item, string $field, array $details): void
    {
        $value = request()->input($field);

        $this->validateSEOInformation($field, $value);

        $url_to_store = isset($value['url']) && !empty($value['url']) ? $value['url'] : '/';
        if (isset($value['prefix']) && !empty($value['prefix'])) {
            $url_to_store = $value['prefix'] . '/' . $url_to_store;
        }

        $UrlExists = Url::where('url', $url_to_store)->first();
        if (!$UrlExists) {
            // If the URL isn't found, we don't need to trigger a validation error
            return;
        }

        // Else, we need to check if the URL matches the current resource
        $ExistingUrl = $this->getItemUrl($Item);
        if ($ExistingUrl && $ExistingUrl->id == $UrlExists->id) {
            // If there is an existing URL, and the IDs match,
            // we don't need a validation error
            return;
        }

        // Otherwise, we need to trigger a warning that the URL already exists
        throw ValidationException::withMessages([
            $field => 'That URL is already in use, please choose something else.'
        ]);
    }

    /**
     * Validate the request meta title & meta description for 
     * maximum length
     * 
     * @param string $field_name
     * @param mixed $value
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateSEOInformation(string $field_name, mixed $value): void
    {
        // Check meta title & description lengths
        $meta_title = data_get($value, 'meta_title');
        $meta_description = data_get($value, 'meta_description');
        
        $meta_title_max = $this->getMetaTitleLength();
        $meta_description_max = $this->getMetaDescriptionLength();

        $meta_title_err = !empty($meta_title) && strlen($meta_title) > $meta_title_max;
        $meta_description_err = !empty($meta_description) && strlen($meta_description) > $meta_description_max;

        if ($meta_title_err || $meta_description_err) {
            $messages = [];
            if ($meta_title_err) {
                $messages[] = 'the meta title is less than ' . $meta_title_max . ' characters';
            }
            if ($meta_description_err) {
                $messages[] = 'the meta description is less than ' . $meta_description_max . ' characters';
            }
            throw ValidationException::withMessages([
                $field_name => 'Please ensure ' . implode(' and ', $messages) . '.'
            ]);
        }
    }

    /**
     * Centralised function to format a meta title ready for
     * DB storage
     * 
     * @param string $meta_title
     * @return string
     */
    protected function dbFormatMetaTitle(string $meta_title): string
    {
        $meta_title = strip_tags($meta_title);
        $meta_title = str_replace(["\r\n", "\r", "\n"], ' ', $meta_title);
        $meta_title = substr($meta_title, 0, $this->getMetaTitleLength());
        return $meta_title;
    }

    /**
     * Centralised function to format a meta description ready
     * for DB storage
     * 
     * @param string $meta_description
     * @return string
     */
    protected function dbFormatMetaDescription(string $meta_description): string
    {
        $meta_description = strip_tags($meta_description);
        $meta_description = str_replace(["\r\n", "\r", "\n"], ' ', $meta_description);
        $meta_description = substr($meta_description, 0, $this->getMetaDescriptionLength());
        return $meta_description;
    }

    /**
     * Centralised function to get meta title DB
     * column maximmum length
     * 
     * @return int
     */
    protected function getMetaTitleLength(): int
    {
        $TitleCol = TableHelper::getTableColumn('urls', 'meta_title');
        if ($TitleCol && $TitleCol->type_name == 'varchar') {
            preg_match('/varchar\((\d+)\)/i', $TitleCol->type, $length_matches);
            if (!empty($length_matches) && !empty($length_matches[1])) {
                return (int)$length_matches[1];
            }
        }
        
        return self::DEFAULT_META_TITLE_MAX;
    }

    /**
     * Centralised function to get meta description DB
     * column maximmum length
     * 
     * @return int
     */
    protected function getMetaDescriptionLength(): int
    {
        $DescriptionCol = TableHelper::getTableColumn('urls', 'meta_description');
        if ($DescriptionCol && $DescriptionCol->type_name == 'varchar') {
            preg_match('/varchar\((\d+)\)/i', $DescriptionCol->type, $length_matches);
            if (!empty($length_matches) && !empty($length_matches[1])) {
                return (int)$length_matches[1];
            }
        }

        return self::DEFAULT_META_DESCRIPTION_MAX;
    }
}