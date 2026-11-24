<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Adm\Models\ChangeLog;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class Edit extends RequestHandler
{
    /**
     * Configuration of form field groups (tabs) & 
     * the fields within them
     * 
     * @var array
     */
    protected $form_field_groups    = [];

    /**
     * The 'primary' ID of the item being edited
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
     * Contruct an instance of the request handler,
     * assigning it's ID to the internal property
     * 
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->item_id = $id;
    }

    /**
     * Handle the request & load the edit form
     * 
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function handle(): View|RedirectResponse
    {
        $this->validateSetFormLayout();
        $this->validateSetItem();

        return view(
            $this->getConfigProperty('visual_form', false) ? 'crud.visual-editor' : 'crud.form',
            [
                'title'         => $this->getConfigProperty('title'),
                'breadcrumbs'   => $this->generateBreadcrumbs('edit', $this->item_id),
                'form_fields'   => $this->form_field_groups,
                'Item'          => $this->Item,
                'form_action'   => $this->getFormAction(),
                'back_button'   => [
                    'text'          => $this->getConfigProperty('back_button_text', 'Back'),
                    'url'           => $this->getCrudUrl($this->getConfigProperty('url_slug'), '', null),
                    'enabled'       => $this->getConfigProperty('back_button_enabled', true)
                ],
                'preview_button'    => [
                    'enabled'           => $this->isPreviewAvailable(),
                    'url'               => route('adm.route.preview', [$this->getConfigProperty('url_slug'), $this->item_id])
                ],
                'save_back_button'  => [
                    'enabled'           => $this->getConfigProperty('save_back_button_enabled', true),
                    'text'              => $this->getConfigProperty('save_back_button_text', 'Save &amp; go back')
                ],
                'save_continue_button'  => [
                    'enabled'           => $this->getConfigProperty('save_continue_button_enabled', true),
                    'text'              => $this->getConfigProperty('save_continue_button_text', 'Save &amp; continue')
                ],
                'has_wysiwyg'   => $this->formHasWysiwyg($this->form_field_groups),
                'latest_edit'   => $this->getLatestEditDetails($this->Item),

                'broadcast_channel'     => $this->getClassBroadcastChannel()
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

                if (isset($fields[$field_name]['validation'])) {
                    if(
                        !isset($fields[$field_name]['validation']['rules'])
                        || isset($fields[$field_name]['validation']['rules']) 
                        && !is_array($fields[$field_name]['validation']['rules'])
                    ) {
                        throw new InvalidArgumentException(sprintf('Validation rules for: \'%s\' have not been set in group: \'%s\'', $field_name, $group));
                    }
                    if(
                        !isset($fields[$field_name]['validation']['messages'])
                        || isset($fields[$field_name]['validation']['messages']) 
                        && !is_array($fields[$field_name]['validation']['messages'])
                    ) {
                        throw new InvalidArgumentException(sprintf('Validation messages for: \'%s\' have not been set in group: \'%s\'', $field_name, $group));
                    }
                }

                if (!isset($form_fields[$group])) {
                    $form_fields[$group] = [];
                }
                $fields[$field_name]['required'] = $this->isFieldRequired($fields[$field_name]);
                if (
                    $fields[$field_name]['required'] 
                    && isset($fields[$field_name]['validation']['update_rules'])
                    && !in_array('required', $fields[$field_name]['validation']['update_rules'])
                ) {
                    $fields[$field_name]['required'] = false;
                }
                $form_fields[$group][$field_name] = $fields[$field_name];
            }
        }

        $this->form_field_groups = $form_fields;
    }

    /**
     * Load the item being edited
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

        if ($this->itemHasVersioning($Item)) {
            $Qry = $item_class::query()->withoutGlobalScopes();
            $this->CrudInterface->applyLanguageCondition($Qry);
            $Item = $Qry->where($this->getClassIDField(), $this->item_id)
                ->orderBy('version', 'desc')
                ->first();
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
                $Field->prepareForForm($Item, $field_name, $field_details);

                $this->form_field_groups[$group_name][$field_name] = $Field->modifyFieldDetailsForForm($field_details, $Item);
            }
        }
    }

    /**
     * Return the CMS form action URL
     * 
     * @return string
     */
    protected function getFormAction(): string
    {
        return $this->getCrudUrl($this->getConfigProperty('url_slug'), 'update', $this->item_id);
    }

    /**
     * Get details of the latest edits for
     * the provided item
     * 
     * @param \Illuminate\Database\Eloquent\Model
     * @return array
     */
    protected function getLatestEditDetails(Model $Item): array
    {
        $details    = [
            'edited_at' => null,
            'edited_by' => null
        ];

        $ChangeLog = ChangeLog::where([
            'changeable_type'   => $this->getConfigProperty('class'),
            'changeable_id'     => $this->item_id,
            'language_id'       => data_get($Item, 'language_id')
        ])
        ->orderBy('created_at', 'desc')
        ->first();

        if ($ChangeLog) {
            $details['edited_at'] = $ChangeLog->created_at->format("g:ia \o\\n jS F Y");
            $details['edited_by'] = $ChangeLog->adm_user ? $ChangeLog->adm_user->name : '';
        }

        return $details;
    }
}
