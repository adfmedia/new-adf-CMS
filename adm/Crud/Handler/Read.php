<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Request handler used to process CMS read pages
 */
class Read extends RequestHandler
{
    /**
     * Configuration of form field groups (tabs) & 
     * the fields within them
     * 
     * @var array
     */
    protected $form_field_groups    = [];

    /**
     * The 'primary' ID of the item being read
     * 
     * @var ?int
     */
    protected $item_id  = null;

    /**
     * The item being edited
     * 
     * @var ?Model
     */
    protected $Item;

    /**
     * Construct a new instance of the request handler,
     * setting the item ID into the internal property
     * 
     * @var int $id
     */
    public function __construct(int $id)
    {
        $this->item_id = $id;
    }

    /**
     * Handle the request and load the read page
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function handle(): View
    {
        $this->validateSetFormLayout();
        $this->validateSetItem();

        return view(
            'crud.read',
            [
                'title'         => $this->getConfigProperty('title'),
                'breadcrumbs'   => $this->generateBreadcrumbs('read', $this->item_id),
                'form_fields'   => $this->form_field_groups,
                'Item'          => $this->Item,
                'back_button'   => [
                    'text'          => $this->getConfigProperty('back_button_text', 'Back'),
                    'url'           => $this->getCrudUrl($this->getConfigProperty('url_slug'), '', null),
                    'enabled'       => $this->getConfigProperty('back_button_enabled', true)
                ]
            ]
        );
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
                $form_fields[$group][$field_name] = $fields[$field_name];
            }
        }

        $this->form_field_groups = $form_fields;
    }

    /**
     * Load the item being read
     * 
     * @return void
     * @throws \Exception
     */
    protected function validateSetItem(): void
    {
        $item_class = $this->getConfigProperty('class');
        $Qry = $item_class::query()->withoutGlobalScopes();
        $this->CrudInterface->applyLanguageCondition($Qry);
        $Item = $Qry->where($this->getClassIDField(), $this->item_id)->first();

        if (!$Item) {
            throw new Exception($this->getConfigProperty('title') . ' not found with ID ' . $this->item_id);
        }

        $this->processItem($Item);
        $this->Item = $Item;
    }

    /**
     * Process the new item instance, and prepare it's fields
     * ready for the form
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return void
     */
    protected function processItem(Model $Item): void
    {
        foreach ($this->form_field_groups as $group_name => $group_fields) {
            foreach ($group_fields as $field_name => $field_details) {
                $Field = new CrudField($this->CrudInterface, $field_details['type']);
                $Field->prepareForRead($Item, $field_name, $field_details);

                $this->form_field_groups[$group_name][$field_name] = $Field->modifyFieldDetailsForForm($field_details, $Item);
            }
        }
    }
}