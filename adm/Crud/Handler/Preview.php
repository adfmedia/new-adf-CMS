<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Adm\Helpers\ViewHelper;
use App\Http\Controllers\RouteController;
use App\Models\AssignedSizedFile;
use App\Models\PageBuilderBlock;
use App\Models\Url;
use Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

class Preview extends RequestHandler
{
    /**
     * Configuration of form field groups (tabs) & 
     * the fields within them
     * 
     * @var array
     */
    protected $form_field_groups    = [];

    /**
     * The 'primary' ID of the item being previewed
     * 
     * @var ?int
     */
    protected $item_id;

    /**
     * The item being previewed
     * 
     * @var ?Model
     */
    protected $Item;

    /**
     * Construct a new instance of the request handler
     * and set the previewed item ID
     * 
     * @param ?int $item_id
     */
    public function __construct(?int $item_id = null)
    {
        $this->item_id = $item_id;
    }

    /**
     * Handle the request and load the preview screen
     * 
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function handle(): Response|View
    {
        $this->validateSetFormLayout();
        $this->validateSetItem();

        ViewHelper::setViews('app');
        $RouteController = new RouteController();
        try {
            $response = $RouteController->cmsPreview($this->getItemUrl(), $this->Item);
            if ($response instanceof \Illuminate\View\View) {
                $response = response($response->render());
            }
        } catch (Error $e) {
            if ($this->getConfigProperty('visual_form')) {
                /** @var \Adm\Models\AdmUser */
                $User = Auth::guard('adm')->user();

                throw $e;

                ViewHelper::setViews('adm');
                $response = view(
                    'crud.errors.visual-editor.unavailable', 
                    [
                        'e'             => $e,
                        'super_admin'   => $User->is_super_admin
                    ]
                );
            } else {
                $this->cleanUp($this->Item);
                throw $e;
            }
        }

        $this->cleanUp($this->Item);

        return $response;
    }

    /**
     * Validate the configured form field layout
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateSetFormLayout(): void
    {
        if (!$this->Config->hasProperty('form_field_layout')) {
            throw new InvalidArgumentException('No form_field_layout config directive found.');
        }

        $form_fields = [];
        $fields = $this->getConfigProperty('fields');
        $form_layout = $this->getConfigProperty('form_field_layout');

        if ($this->configClassHasMethod('dynamicChangeFormLayout')) {
            $form_layout = $this->Config->getMethod('dynamicChangeFormLayout')->invoke(null, $form_layout, $this->item_id);
        }

        foreach ($form_layout as $group => $group_fields) {
            foreach ($group_fields as $field_name) {
                if (!isset($fields[$field_name])) {
                    throw new InvalidArgumentException('Field definition for ' . $field_name . ' missing - found in group ' . $group);
                }

                if (!isset($form_fields[$group])) {
                    $form_fields[$group] = [];
                }
                $fields[$field_name]['required'] = $this->isFieldRequired($fields[$field_name]);
                $form_fields[$group][$field_name] = $fields[$field_name];
            }
        }

        $this->form_field_groups = $form_fields;
    }

    /**
     * Load the item being previewed
     * 
     * @return void
     */
    protected function validateSetItem(): void
    {
        $item_class = $this->getConfigProperty('class');

        if (!is_null($this->item_id)) {
            $Qry = $item_class::query()->withoutGlobalScopes();
            $this->CrudInterface->applyLanguageCondition($Qry);
            $Item = $Qry->where($this->getClassIDField(), $this->item_id)->first();
        }

        if (!isset($Item)) {
            $Item = new $item_class();
            data_set($Item, $this->getClassIDField(), -1);
            data_set($Item, 'language_id', 1);
        }

        $Item->version = -1;

        $this->processItem($Item);
        $this->Item = $Item;
    }

    /**
     * Process the new item instance, and prepare it's fields
     * ready for preview
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return void
     */
    protected function processItem(Model $Item): void
    {
        if (!request()->isMethod('post')) { return; }

        foreach ($this->form_field_groups as $group_name => $group_fields) {
            foreach ($group_fields as $field_name => $field_details) {
                $Field = new CrudField($this->CrudInterface, $field_details['type']);
                $Field->prepareForStorage($Item, $field_name, request()->input($field_name), $field_details);
                if ($Field->runAfterStoredInPreviewHandler()) {
                    $Field->afterStored($Item, $field_name, request()->input($field_name), $field_details);
                }
            }
        }
    }

    /**
     * Clean up any residual data stored after loading
     * the preview page
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return void
     */
    protected function cleanUp(Model $Item): void
    {
        // Remove any URLs stored with a URLable ID of 0
        Url::where([
            'urlable_id'    => 0,
            'urlable_type'  => $this->getConfigProperty('class')
        ])->forceDelete();

        // Remove any blocks stored with -1 version
        $Blocks = PageBuilderBlock::where([
            'morphable_id'      => data_get($Item, $this->CrudInterface->getClassIDField()),
            'morphable_type'    => $this->getConfigProperty('class'),
            'language_id'       => data_get($Item, 'language_id'),
            'version'           => -1
        ])->get();
        foreach ($Blocks as $Block) {
            $Block->contents()->delete();
            $Block->forceDelete();
        }

        // Clean up any sized images
        AssignedSizedFile::where([
            'morphable_id'      => data_get($Item, $this->CrudInterface->getClassIDField()),
            'morphable_type'    => $this->getConfigProperty('class'),
            'language_id'       => data_get($Item, 'language_id'),
            'version'           => -1
        ])->forceDelete();
    }

    /**
     * Get the URL for the item being previewed
     * 
     * @return \App\Models\Url
     */
    protected function getItemUrl(): Url
    {
        if ($this->classHasUrl()) {
            $Url = data_get($this->Item, 'url_record', new Url());
        } else {
            $Url = new Url();
        }

        $path_field_name = null;
        foreach ($this->getConfigProperty('fields') as $field_name => $field_details) {
            if ($field_details['type'] == 'path') {
                $path_field_name = $field_name;
            }
        }
        
        if (!empty($path_field_name) && request()->input($path_field_name)) {
            $url_details = request()->input($path_field_name);
            if (!empty($url_details['url'])) {
                $Url->url = $url_details['url'];
            }
            if (!empty($url_details['meta_title'])) {
                $Url->meta_title = $url_details['meta_title'];
            }
            if (!empty($url_details['meta_description'])) {
                $Url->meta_description = $url_details['meta_description'];
            }
        }

        $Url->no_index = true;

        return $Url;
    }
}