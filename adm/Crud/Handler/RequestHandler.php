<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Adm\Crud\CrudInterface;
use Adm\Exceptions\AdmPermissionException;
use Adm\Models\ChangeLog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Base class used to handle the 'generic' element of the current request,
 * before sending through to the specific handler
 */
class RequestHandler
{
    /**
     * Instance of the CrudInterface for use in request handling
     * 
     * @var \Adm\Crud\CrudInterface
     */
    protected $CrudInterface;

    /** 
     * The current configuration to process & handle
     * 
     * @var \ReflectionClass
     * */
    protected $Config;

    /**
     * Process the current request
     * 
     * @param \Adm\Crud\CrudInterface $CrudInterface
     * @param mixed $method
     * @param mixed $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function processRequest(CrudInterface $CrudInterface, mixed $method, mixed $id): View|Response|RedirectResponse|StreamedResponse|JsonResponse|BinaryFileResponse
    {
        $this->CrudInterface = $CrudInterface;

        $this->validateConfig($CrudInterface);
        $this->checkUserAuth($method, $id);
        $Handler = $this->getHandler($method, $id);
        $Handler->setCrudAndConfig($this->CrudInterface, $this->Config);
        return $Handler->handle();
    }

    /**
     * Assigns the provided Crud interface & Config class
     * 
     * @param \Adm\Crud\CrudInterface $CrudInterface
     * @param \ReflectionClass $Config
     * @return void
     */
    protected function setCrudAndConfig(CrudInterface $CrudInterface, ReflectionClass $Config): void
    {
        $this->CrudInterface = $CrudInterface;
        $this->Config = $Config;
    }

    /**
     * Check if the currently logged in user has access
     * to the requested section. Throws exception if not
     * 
     * @param mixed $method
     * @param mixed $id
     * @return void
     * @throws \Adm\Exceptions\AdmPermissionException
     */
    protected function checkUserAuth(mixed $method, mixed $id): void
    {
        if ($this->Config->hasMethod('authCheck')) {
            $custom_check = $this->Config->getMethod('authCheck')->invoke(null, $method, $id);
            if ($custom_check === true) {
                return;
            }
        }
        
        /** @var \Adm\Models\AdmUser */
        $User = Auth::guard('adm')->user();
        $current_area = $this->Config->getStaticPropertyValue('url_slug');
        if (!$User->canAccess($current_area)) {
            throw new AdmPermissionException();
        }
    }

    /**
     * Validates the current configuration object, throwing an error if invalid
     * 
     * @param \Adm\Crud\CrudInterface $CrudInterface
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateConfig(CrudInterface $CrudInterface): void
    {
        $config = $CrudInterface->getConfig();
        if (!class_exists($config)) {
            throw new InvalidArgumentException('Config ' . $config . ' does not exist.');
        }

        $Config = $this->getConfigClass($CrudInterface);
        if (!$Config->hasProperty('class')) {
            throw new InvalidArgumentException('Config ' . $config . ' has no class assigned.');
        }
        if (!$Config->hasProperty('fields')) {
            throw new InvalidArgumentException('Config ' . $config . ' has no fields assigned.');
        }

        $class = $Config->getStaticPropertyValue('class');
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Class ' . $class . ' does not exist.');
        }

        $this->Config = $Config;
    }

    /**
     * Return an accessible reflection class of the current configuration
     * object
     * 
     * @param \Adm\Crud\CrudInterface $CrudInterface
     * @return \ReflectionClass
     */
    protected function getConfigClass($CrudInterface): ReflectionClass
    {
        return $CrudInterface->getConfigClass();
    }

    /**
     * Returns a static property value from the current configuration
     * object, optionally with a default value if not found
     * 
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    protected function getConfigProperty(string $property, mixed $default = null): mixed
    {
        return $this->Config->getStaticPropertyValue($property, $default);
    }

    /**
     * Return a 'singular' version of a property value string from
     * the current configuration object
     * 
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    protected function getSingularConfigProperty(string $property, mixed $default = null): mixed
    {
        $prop = $this->Config->getStaticPropertyValue($property, $default);
        if (!is_string($prop)) { return $prop; }

        return Str::singular($prop);
    }

    /**
     * Checks whether the current configuration class has
     * the provided method
     * 
     * @param string $method
     * @return bool
     */
    protected function configClassHasMethod(string $method): bool
    {
        return $this->Config->hasMethod($method);
    }

    /**
     * Return boolean indicating whether a field is required
     * based on it's validation rules
     * 
     * @param array $field_details
     * @return bool
     */
    protected function isFieldRequired(array $field_details): bool
    {
        return in_array('required', (array)data_get($field_details, 'validation.rules'));
    }

    /**
     * Checks whether the current form field set has a WYSIWYG
     * field or not
     * 
     * @param array $form_fields
     * @return bool
     */
    protected function formHasWysiwyg(array $form_fields): bool
    {
        foreach ($form_fields as $group => $fields) {
            foreach ($fields as $field) {
                if ($field['type'] == 'wysiwyg') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return a URL for a particular section of the CMS
     * 
     * @param string $slug
     * @param string $method
     * @param mixed $id
     * @return string
     */
    protected function getCrudUrl(string $slug, string $method, mixed $id): string
    {
        return $this->CrudInterface->getUrlRoute($slug, $method, $id);
    }

    /**
     * Return query string containing common URL parameters,
     * optionally with page number
     * 
     * @param bool $include_page
     * @return string
     */
    protected function getCommonUrlParameters(bool $include_page = false): string
    {
        $qs = '';

        $params = [];
        $params_to_append = [
            'sort',
            'sortdir',
            'search'
        ];
        if ($include_page) {
            $params_to_append[] = 'page';
        }
        foreach ($params_to_append as $get_param) {
            if (request()->query($get_param)) {
                $params[$get_param] = request()->query($get_param);
            }
        }
        if (!empty($params)) {
            $qs = '?' . http_build_query($params);
        }

        return $qs;
    }

    /**
     * Return the main ID field for the current class
     * If the class uses a composite primary key, return the ID that isn't
     * related to language or version
     * 
     * @return string
     */
    protected function getClassIDField(): string
    {
        return $this->CrudInterface->getClassIDField();
    }

    /**
     * Return the database table name for the current class
     * 
     * @return string
     */
    protected function getClassTableName(): string
    {
        return $this->CrudInterface->getClassTableName();
    }

    /**
     * Checks whether the current item class has the SoftDeletes
     * trait
     * 
     * @return bool
     */
    protected function classHasSoftDeletes(): bool
    {
        $class = $this->getConfigProperty('class');
        return $class::isSoftDeletable();
    }

    /**
     * Checks whether the current item class has the HasUrl
     * trait
     * 
     * @return bool
     */
    protected function classHasUrl(): bool
    {
        return in_array('App\Models\Traits\HasUrl', class_uses($this->getConfigProperty('class')));
    }

    /**
     * Checks whether the provided item has versioning control
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return bool
     */
    protected function itemHasVersioning(Model $Item): bool
    {
        if ($Item->getIncrementing()) { return false; }

        return in_array('version', (array)$Item->getKeyName()) && $this->classHasSoftDeletes();
    }

    /**
     * Check whether previews are available as an option
     * 
     * @return bool
     */
    protected function isPreviewAvailable(): bool
    {
        $available = true;
        if (!$this->classHasUrl()) {
            $available = false;
        }
        if ($this->getConfigProperty('preview_disabled', false)) {
            $available = false;
        }

        $item_class = $this->getConfigProperty('class');
        $Item = new $item_class();
        if (!$this->itemHasVersioning($Item)) {
            $available = false;
        }

        return $available;
    }

    /**
     * Get the JS broadcast channel name for the current config class
     * 
     * @return string
     */
    protected function getClassBroadcastChannel(): string
    {
        return str_replace('\\', '-', $this->getConfigProperty('class'));
    }

    /**
     * Generate breadcrumbs for the current request
     * 
     * @param string $method
     * @param mixed $id
     * @return array
     */
    protected function generateBreadcrumbs(string $method, mixed $id): array
    {
        $crumbs = [];
        $crumbs[] = [
            'url'   => route('adm.route'),
            'title' => 'Dashboard'
        ];
        $crumbs[] = [
            'url'   => $this->getCrudUrl($this->getConfigProperty('url_slug'), '', null),
            'title' => $this->getConfigProperty('title')
        ];

        switch ($method) {
            case 'edit':
                $crumbs[] = [
                    'url'   => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'edit', $id),
                    'title' => 'Edit ' . Str::lower(Str::singular(($this->getConfigProperty('title'))))
                ];
                break;
            case 'copy':
                $crumbs[] = [
                    'url'   => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'copy', $id),
                    'title' => 'Copy ' . Str::lower(Str::singular(($this->getConfigProperty('title'))))
                ];
                break;
            case 'read':
                $crumbs[] = [
                    'url'   => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'read', $id),
                    'title' => 'View ' . Str::lower(Str::singular(($this->getConfigProperty('title'))))
                ];
                break;
            case 'create':
                $crumbs[] = [
                    'url'   => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'create', null),
                    'title' => 'Create ' . Str::lower(Str::singular(($this->getConfigProperty('title'))))
                ];
                break;
        }

        return $crumbs;
    }

    /**
     * Return the relevant request handler for the current request
     * 
     * @param string $method
     * @param mixed $id
     * @return \Adm\Crud\Handler\RequestHandler
     * @throws \InvalidArgumentException
     */
    private function getHandler(mixed $method, mixed $id): RequestHandler
    {
        switch ($method) {
            case 'export':
                return new Export();
            case 'delete':
                return new Delete();
            case 'update':
                if (!$id) {
                    throw new InvalidArgumentException('ID parameter is required for updates.');
                }
                return new Update($id);
            case 'store':
                return new Store();
            case 'edit':
                if (!$id) {
                    throw new InvalidArgumentException('ID parameter is required for edits.');
                }
                return new Edit($id);
            case 'copy':
                if (!$id) {
                    throw new InvalidArgumentException('ID parameter is required for copies.');
                }
                return new Copy($id);
            case 'create':
                return new Create();
            case 'read':
                if (!$id) {
                    throw new InvalidArgumentException('ID parameter is required for reads.');
                }
                return new Read($id);
            case 'preview':
                if (!$id) { $id = null; }
                return new Preview($id);
            case 'translate':
                if (!$id) {
                    throw new InvalidArgumentException('ID parameter is required for translations.');
                }
                return new Translate($id);
            case 'translate-store':
                if (!$id) {
                    throw new InvalidArgumentException('ID parameter is required for translations.');
                }
                return new TranslateStore($id);
            case 'listing':
            default:
                return new Listing();
        }
    }

    /**
     * Handle the current request
     * 
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function handle(): View|Response|RedirectResponse|StreamedResponse|JsonResponse|BinaryFileResponse
    {
        return response()->redirectTo(route('adm.route'));
    }

    /**
     * Return a redirect back response with validation errors
     * and a flash message
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function returnValidationErrorResponse(Validator $Validator): RedirectResponse
    {
        return redirect()
            ->back()
            ->withErrors($Validator)
            ->withInput()
            ->with('error', 'Please correct any validation errors and try again.');
    }

    /**
     * Filter the current CMS listing query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $item_class
     * @param array $columns
     * @return void
     */
    public function filterItemsQuery(Builder $query, string $item_class, array $columns): void
    {
        $search_term = request()->query('search');
        if (!$search_term) { return; }

        if (method_exists($item_class, 'scopeCmsSearch')) {
            $query->cmsSearch($search_term);
            return;
        }

        $Crud = $this->CrudInterface;
        $table = $this->getClassTableName();
        $query->where(function (Builder $subquery) use ($Crud, $table, $columns, $search_term) {
            foreach ($columns as $field_name => $details) {
                $Field = new CrudField($Crud, $details['type']);
                $Field->search($subquery, $table, $field_name, $search_term, $details);
            }
        });
    }

    /**
     * Sort the current CMS listing query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $columns
     * @return void
     */
    public function sortItemsQuery(Builder $query, array $columns): void
    {
        $sort_by = $this->CrudInterface->getItemSortField();
        if (!$sort_by) { return; }

        $sort_dir = $this->CrudInterface->getItemSortDirection();
        foreach ($columns as $field_name => $details) {
            if ($sort_by == $field_name) {
                $Field = new CrudField($this->CrudInterface, $details['type']);
                $Field->sort($query, $this->getClassTableName(), $field_name, $sort_dir, $details);
            }
        }
    }

    /**
     * Flush the site & database cache
     * 
     * @return void
     */
    protected function flushCache(): void
    {
        $this->CrudInterface->flushCache();
    }

    /**
     * Log that the current item has been changed
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @param string $change_type
     * @return void
     */
    protected function logChange(Model $Item, string $change_type): void
    {
        $AdmUser = Auth::guard('adm')->user();
        if ($AdmUser->is_super_admin) { return; }
        if (!is_int(data_get($Item, $this->getClassIDField()))) { return; }

        ChangeLog::create([
            'changeable_type'   => get_class($Item),
            'changeable_id'     => data_get($Item, $this->getClassIDField()),
            'language_id'       => data_get($Item, 'language_id', 0),
            'change_type'       => $change_type,
            'adm_user_id'       => $AdmUser->id
        ]);
    }
}