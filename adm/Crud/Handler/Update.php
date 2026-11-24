<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Adm\Library\Database\TableHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorInstance;

class Update extends RequestHandler
{
    use ValidatesRequests;

    /**
     * The 'primary' ID of the item being updated
     * 
     * @var ?int
     */
    protected $item_id  = null;

    /**
     * The fields to update
     * 
     * @var array
     */
    protected $fields   = [];

    /**
     * The item being updated
     * 
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $Item;

    /**
     * Construct a new instance of the request handler,
     * assigning the item ID to the internal property
     * 
     * @var int $id
     */
    public function __construct(int $id)
    {
        $this->item_id = $id;
    }

    /**
     * Handle the request and update the item
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(): RedirectResponse
    {
        $this->fields = $this->getConfigProperty('fields');
        $Item = $this->validateGetItem();
        
        $Validator = $this->validateRequest($Item);
        if ($Validator->fails()) {
            return $this->returnValidationErrorResponse($Validator);
        }

        $this->assignPrimaryKeyValues($Item);

        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            $Field->prepareForStorage($Item, $field_name, request()->input($field_name), $field_details);
        }

        if ($this->configClassHasMethod('beforeUpdated')) {
            $this->Config->getMethod('beforeUpdated')->invoke(null, $Item);
        }
        if ($this->configClassHasMethod('beforeSaved')) {
            $this->Config->getMethod('beforeSaved')->invoke(null, $Item);
        }

        $Item->save();

        $this->itemVersioning($Item);

        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            $Field->afterStored($Item, $field_name, request()->input($field_name), $field_details);
        }

        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            $Field->postAfterStored($Item, $field_name, request()->input($field_name), $field_details);
        }

        if ($this->configClassHasMethod('afterUpdated')) {
            $this->Config->getMethod('afterUpdated')->invoke(null, $Item);
        }
        if ($this->configClassHasMethod('afterSaved')) {
            $this->Config->getMethod('afterSaved')->invoke(null, $Item);
        }

        $this->logChange($Item, 'Updated');
        $this->flushCache();

        return response()
            ->redirectTo($this->getRedirectUrl())
            ->with('success', $this->getSingularConfigProperty('title') . ' updated successfully.');
    }

    /**
     * Load the item being updated
     * 
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    protected function validateGetItem(): Model
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

            $this->CrudInterface->setOldItem($Item);

            $NewItem = new $item_class();
            foreach ((array)$Item->getKeyName() as $pk_part) {
                data_set($NewItem, $pk_part, data_get($Item, $pk_part));
            }
            $Item = $NewItem;
        }

        return $Item;
    }

    /**
     * Validate the request and form fields
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return \Illuminate\Validation\Validator
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateRequest(Model $Item): ValidatorInstance
    {
        $rules      = [];
        $messages   = [];

        $fillable_fields = $Item->getFillable();
        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            [$field_rules, $field_messages] = $Field->validate($field_name, $field_details, $Item, true);

            if (
                in_array($field_name, $fillable_fields) && 
                (!isset($field_details['validation']['bypass_update_required']) || !$field_details['validation']['bypass_update_required'])
            ) {
                $Column = TableHelper::getTableColumn($Item->getTable(), $field_name);
                if (!$Column->getNullable() && $Column->getDefault() == null) {
                    $field_rules[] = 'required';
                }
            }

            $rules[$field_name] = $field_rules;
            $messages[$field_name] = $field_messages;
        }

        if ($this->configClassHasMethod('getUpdateValidation')) {
            [$rules, $messages] = $this->Config->getMethod('getUpdateValidation')->invoke(null, $rules, $messages, $Item);
        }

        $Validator = Validator::make(request()->all(), $rules, $messages);

        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            $Field->validateAfter($Item, $field_name, $field_details);
        }

        return $Validator;
    }

    /**
     * Assign primary key values to the item being updated
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return void
     */
    protected function assignPrimaryKeyValues(Model $Item): void
    {
        // If auto-incrementing, we don't need to set anything
        if ($Item->getIncrementing()) { return; }

        // As we're updating the record, we don't need to change the ID or language
        foreach ((array)$Item->getKeyName() as $pk_part) {
            switch ($pk_part) {
                case 'version': data_set($Item, 'version', ((int)data_get($Item, 'version')+1)); break;
            }
        }
    }

    /**
     * If the item being updated has a version number,
     * remove any versions that we don't need based on
     * ENV configuration settings
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return void
     */
    protected function itemVersioning(Model $Item): void
    {
        if (!$this->itemHasVersioning($Item)) { return; }

        $item_class = $this->getConfigProperty('class');

        $pk_conditions = [
            $this->getClassIDField()    => $this->item_id
        ];
        if ($this->CrudInterface->classHasLanguageId()) {
            $pk_conditions['language_id'] = data_get($Item, 'language_id');
        }

        // (Soft)Delete the previous version
        if ($Item->version > 1) {
            $conditions = $pk_conditions;
            $conditions['version'] = ($Item->version - 1);
            $PrevItem = $item_class::query()->withoutGlobalScopes()
                ->withTrashed()
                ->where($conditions)->first();
            if ($PrevItem) {
                $PrevItem->delete();
            }
        }

        // Check the number of stored versions
        $total_versions = $item_class::query()->withoutGlobalScopes()
            ->withTrashed()
            ->where($pk_conditions)
            ->count();
        if ($total_versions > config('database.max_stored_versions')) {
            $item_class::query()->withoutGlobalScopes()
                ->withTrashed()
                ->where($pk_conditions)
                ->orderBy('version', 'asc')
                ->limit(1)
                ->forceDelete();
        }
    }

    /**
     * Return the URL to redirect to after saving the item
     * 
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        if (request()->has('save_continue')) {
            return $this->getCrudUrl($this->getConfigProperty('url_slug'), 'edit', $this->item_id);
        }
        return $this->getCrudUrl($this->getConfigProperty('url_slug'), '', null);
    }
}
