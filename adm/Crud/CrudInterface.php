<?php

namespace Adm\Crud;

use Adm\Crud\Handler\RequestHandler;
use Adm\Exceptions\AdmPermissionException;
use Adm\Helpers\ViewHelper;
use Adm\Library\Cloudflare\CFCacheInterface;
use App\Models\Language;
use App\Models\Url;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Modules\Search\Models\SearchIndex;
use ReflectionClass;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrudInterface
{
    /**
     * The configuration class name being applied
     * 
     * @var string
     */
    protected $config;

    /**
     * The current CRUD method being used
     * 
     * @var string
     */
    protected $method;

    /**
     * The current item ID being accessed, or null
     * for new items
     * 
     * @var ?int
     */
    protected $item_id;

    /**
     * When performing an update, this contains the previous
     * version of the item (if versioning is applied)
     * 
     * @var ?Model
     */
    protected $OldItem;

    /**
     * The language DB record for the current CMS language
     * 
     * @var Language
     */
    protected $LangRecord;

    /**
     * Boot up the CMS CRUD interface, with the provided
     * handler method, item ID & configuration class
     * 
     * @var ?string $method
     * @var ?int $id
     * @var string $config
     * @return @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function boot(?string $method, ?int $id, string $config): \Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->LangRecord = $this->getLanguage();

        $this->method = $method;
        $this->config = $config;
        $this->item_id = $id;
        return $this->handleMethod($method, $id);
    }

    /**
     * Return the current configuration class name
     * 
     * @return string
     */
    public function getConfig(): string
    {
        return $this->config;
    }

    /**
     * Return an accessible reflection class of the current configuration
     * object
     * 
     * @return \ReflectionClass
     */
    public function getConfigClass(): ReflectionClass
    {
        return new ReflectionClass($this->getConfig());
    }

    /**
     * Return the current configuration class's class name
     * 
     * @return string
     */
    public function getConfigClassName(): string
    {
        $ConfigClass = $this->getConfigClass();
        return $ConfigClass->getStaticPropertyValue('class');
    }

    /**
     * Return the main ID field for the current class
     * If the class uses a composite primary key, return the ID that isn't
     * related to language or version
     * 
     * @return string
     */
    public function getClassIDField(): string
    {
        $ConfigClass = $this->getConfigClass();
        $item_class_name = $ConfigClass->getStaticPropertyValue('class');
        return $this->getPrimaryIDForClass($item_class_name);
    }

    /**
     * Return the database table name for the current class
     * 
     * @param ?string $item_class_name
     * @return string
     */
    public function getClassTableName(?string $item_class_name = null): string
    {
        if (is_null($item_class_name)) {
            $item_class_name = $this->getConfigClassName();
        }

        $ItemClass = new $item_class_name();
        return $ItemClass->getTable();
    }

    /**
     * Return the current 'main' primary ID field for the current class
     * If the class has a composite primary key, return the key that is
     * not language_id or version
     * 
     * @param string $item_class_name
     * @return string
     */
    public function getPrimaryIDForClass(string $item_class_name): string
    {
        $ItemClass = new $item_class_name();
        $primaryKey = $ItemClass->getKeyName();

        if (is_array($primaryKey)) {
            foreach ($primaryKey as $key_field) {
                if (in_array($key_field, ['language_id', 'version'])) {
                    continue;
                }
                return $key_field;
            }
        }

        return $primaryKey;
    }

    /**
     * Determine if the provided class has a language_id field
     * 
     * @param ?string $item_class_name
     * @return bool
     */
    public function classHasLanguageId(?string $item_class_name = null): bool
    {
        if (is_null($item_class_name)) {
            $item_class_name = $this->getConfigClassName();
        }
        
        $class_inst = new $item_class_name();
        $class_pk = $class_inst->getKeyName();

        if (!is_array($class_pk)) {
            return false;
        }
        return in_array('language_id', $class_pk);
    }

    /**
     * Apply language_id restraint on the provided query
     * 
     * @param Builder $Query
     * @param ?string $item_class_name
     * @return void
     */
    public function applyLanguageCondition(Builder $Query, ?string $item_class_name = null): void
    {
        if ($this->classHasLanguageId($item_class_name)) {
            $Query->where($this->getClassTableName($item_class_name) . '.language_id', $this->getLanguageId());
        }
    }

    /**
     * Return the current method
     * 
     * @return ?string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Return the current item ID
     * 
     * @return ?int
     */
    public function getCurrentItemId(): ?int
    {
        return $this->item_id;
    }

    /**
     * Returns the current eloquent model's castable attributes
     * 
     * @return array
     */
    public function getClassCasts(): array
    {
        $class_name = $this->getConfigClassName();
        $Model = new $class_name();
        return $Model->getCasts();
    }

    /**
     * Get a configuration class property value, or a default value if not
     * set
     * 
     * @param string $property
     * @param mixed $default_value
     * @return mixed
     */
    public function getConfigClassPropertyOrDefault(string $property, mixed $default_value = null): mixed
    {
        $ConfigClass = $this->getConfigClass();
        return $ConfigClass->hasProperty($property) ? $ConfigClass->getStaticPropertyValue($property) : $default_value;
    }

    /**
     * Get the current list sort field
     * 
     * @return ?string
     */
    public function getItemSortField(): ?string
    {
        return request()->query('sort', $this->getConfigClassPropertyOrDefault('default_sort'));
    }

    /**
     * Get the current list sort direction (asc/desc)
     * 
     * @return string
     */
    public function getItemSortDirection(): string
    {
        return request()->query('sortdir', $this->getConfigClassPropertyOrDefault('default_sort_direction')) == 'desc' ? 'desc' : 'asc';
    }

    /**
     * Handle the provided method & item ID by resolving
     * the request handler
     * 
     * @param ?string $method
     * @param ?int $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function handleMethod(?string $method, ?int $id): View|Response|RedirectResponse|StreamedResponse|JsonResponse|BinaryFileResponse
    {
        $Handler = new RequestHandler();
        try {
            return $Handler->processRequest($this, $method, $id);
        } catch (AdmPermissionException $e) {
            return response()->redirectTo(route('adm.route'));
        }
    }

    /**
     * Returns a string containing the full URL for the 
     * provided slug, method & ID
     * 
     * @param string $slug
     * @param ?string $method
     * @param mixed $id
     * @return string
     */
    public function getUrlRoute(string $slug, ?string $method, mixed $id): string
    {
        $params = ['section' => $slug];
        if (!empty($method)) {
            $params['method'] = $method;
        }
        if (!empty($id)) {
            $params['id'] = $id;
        }

        return route('adm.route', $params);
    }

    /**
     * Sets the old item instance before updating
     * 
     * @param Model $OldItem
     * @return void
     */
    public function setOldItem(Model $OldItem): void
    {
        $this->OldItem = $OldItem;
    }

    /**
     * Returns the old item instance
     * 
     * @return ?Model
     */
    public function getOldItem(): ?Model
    {
        return $this->OldItem;
    }

    /**
     * Get all available languages
     * 
     * @return Collection
     */
    public function getLanguages(): Collection
    {
        return Language::orderBy('title')->get();
    }

    /**
     * Get the current language, or throw an exception if not found.
     * 
     * @return Language
     * @throws Exception
     */
    public function getLanguage(): Language
    {
        if ($this->LangRecord) {
            return $this->LangRecord;
        }

        $current_lang_id = config('app.default_language_id');
        if (request()->session()->has('cms_lang')) {
            $current_lang_id = request()->session()->get('cms_lang');
        }

        $Lang = Language::find($current_lang_id);
        if (!$Lang) {
            request()->session()->forget('cms_lang');
            throw new Exception('Language ' . $current_lang_id . ' not found. Session reset.');
        }

        return $Lang;
    }

    /**
     * Get the current language ID
     * 
     * @return int
     */
    public function getLanguageId(): int
    {
        return $this->getLanguage()->id;
    }

    /**
     * Set the language ID
     * 
     * @param int $language_id
     * @return void
     */
    public function setLanguageId(int $language_id): void
    {
        request()->session()->put('cms_lang', $language_id);
        $this->LangRecord = null;
    }

    /**
     * Check if the current item class is translatable
     * 
     * @param ?string $item_class_name
     * @return bool
     */
    public function isClassTranslatable(?string $item_class_name = null): bool
    {
        if (!$this->classHasLanguageId($item_class_name)) { return false; }
        return $this->getLanguages()->count() > 1;
    }

    /**
     * Flush the site & database cache
     * 
     * @return void
     */
    public function flushCache(): void
    {
        Cache::flush();

        try {
            $CF = new CFCacheInterface();
            $CF->purgeAll();
        } catch (Exception $e) {}
    }

    /**
     * Reindex search indexes
     * 
     * @param ?string $url
     * @return void
     */
    public function reindexSearch(?string $url = null): void
    {
        if ($this->searchAvailable()) {
            ViewHelper::setViews('app');

            $command = 'site_search:index';
            if (!is_null($url)) {
                $command .= ' --url=' . $url;
            }
            Artisan::call($command);

            ViewHelper::setViews();
        }
    }

    /**
     * Removes an item from the search index
     * 
     * @param \App\Models\Url $Url
     * @return void
     */
    public function delistFromSearch(Url $Url): void
    {
        if ($this->searchAvailable()) {
            SearchIndex::where('url_id', $Url->id)->delete();
        }
    }

    /**
     * Check if search is available in the
     * module set
     * 
     * @return bool
     */
    protected function searchAvailable(): bool
    {
        return in_array('Search', config('modules.available'));
    }
}