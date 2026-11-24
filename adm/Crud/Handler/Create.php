<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Create extends RequestHandler
{
    /**
     * Configuration of form field groups (tabs) & 
     * the fields within them
     * 
     * @var array
     */
    protected $form_field_groups    = [];

    /**
     * A model instance of the item being created
     * 
     * @var ?Model
     */
    protected $Item;

    /**
     * Handle the request & load the create form
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function handle(): View
    {
        $this->validateSetFormLayout();
        $this->validateSetItem();

        return view(
            $this->getConfigProperty('visual_form', false) ? 'crud.visual-editor' : 'crud.form',
            [
                'title'         => $this->getConfigProperty('title'),
                'breadcrumbs'   => $this->generateBreadcrumbs('create', null),
                'form_fields'   => $this->form_field_groups,
                'Item'          => $this->Item,
                'form_action'   => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'store', null),
                'back_button'   => [
                    'text'          => $this->getConfigProperty('back_button_text', 'Back'),
                    'url'           => $this->getCrudUrl($this->getConfigProperty('url_slug'), '', null),
                    'enabled'       => $this->getConfigProperty('back_button_enabled', true)
                ],
                'preview_button'    => [
                    'enabled'           => $this->isPreviewAvailable(),
                    'url'               => route('adm.route.preview', [$this->getConfigProperty('url_slug'), null])
                ],
                'save_back_button'  => [
                    'enabled'           => $this->getConfigProperty('save_back_button_enabled', true),
                    'text'              => $this->getConfigProperty('save_back_button_text', 'Save &amp; go back')
                ],
                'save_continue_button'  => [
                    'enabled'           => $this->getConfigProperty('save_continue_button_enabled', true),
                    'text'              => $this->getConfigProperty('save_continue_button_text', 'Save &amp; continue')
                ],
                'has_wysiwyg'   => $this->formHasWysiwyg($this->form_field_groups)
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
            $form_layout = $this->Config->getMethod('dynamicChangeFormLayout')->invoke(null, $form_layout, 0);
        }

        foreach ($form_layout as $group => $group_fields) {
            foreach ($group_fields as $field_name) {
                if (!isset($fields[$field_name])) {
                    throw new InvalidArgumentException('Field definition for ' . $field_name . ' missing - found in group ' . $group);
                }

                if (isset($fields[$field_name]['validation'])) {
                    if(!isset($fields[$field_name]['validation']['rules'])
                    || isset($fields[$field_name]['validation']['rules']) && !is_array($fields[$field_name]['validation']['rules'])) {
                        throw new InvalidArgumentException(sprintf('Validation rules for: \'%s\' have not been set in group: \'%s\'', $field_name, $group));
                    }
                    if(!isset($fields[$field_name]['validation']['messages'])
                    || isset($fields[$field_name]['validation']['messages']) && !is_array($fields[$field_name]['validation']['messages'])) {
                        throw new InvalidArgumentException(sprintf('Validation messages for: \'%s\' have not been set in group: \'%s\'', $field_name, $group));
                    }
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
     * Create a new model instance for the item we're creating
     * and pre-process any form field data
     * 
     * @return void
     */
    protected function validateSetItem(): void
    {
        $item_class = $this->getConfigProperty('class');
        $Item = new $item_class();
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
                $Field->prepareForForm($Item, $field_name, $field_details);
                
                $this->form_field_groups[$group_name][$field_name] = $Field->modifyFieldDetailsForForm($field_details, $Item);
            }
        }
    }
}
