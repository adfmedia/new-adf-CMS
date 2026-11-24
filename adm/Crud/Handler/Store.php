<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Adm\Library\Database\TableHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorInstance;

class Store extends RequestHandler
{
    use ValidatesRequests;

    /**
     * The item ID just stored
     * 
     * @var ?int
     */
    protected $item_id  = null;

    /**
     * The fields to process & store
     * 
     * @var array
     */
    protected $fields   = [];

    /**
     * The item just stored
     * 
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $Item;

    /**
     * Handle the request & store the new item record
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

        if ($this->configClassHasMethod('beforeStored')) {
            $this->Config->getMethod('beforeStored')->invoke(null, $Item);
        }
        if ($this->configClassHasMethod('beforeSaved')) {
            $this->Config->getMethod('beforeSaved')->invoke(null, $Item);
        }

        if ($Item->usesTimestamps()) {
            $Item->created_at = date("Y-m-d H:i:s");
        }
        $Item->save();
        $this->item_id = $Item->{$this->getClassIDField()};

        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            $Field->afterStored($Item, $field_name, request()->input($field_name), $field_details);
        }

        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            $Field->postAfterStored($Item, $field_name, request()->input($field_name), $field_details);
        }

        if ($this->configClassHasMethod('afterStored')) {
            $this->Config->getMethod('afterStored')->invoke(null, $Item);
        }
        if ($this->configClassHasMethod('afterSaved')) {
            $this->Config->getMethod('afterSaved')->invoke(null, $Item);
        }

        $this->logChange($Item, 'Created');
        $this->flushCache();

        return response()
            ->redirectTo($this->getRedirectUrl())
            ->with('success', $this->getSingularConfigProperty('title') . ' created successfully.');
    }

    /**
     * Load a new model instance to store data against
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function validateGetItem(): Model
    {
        $item_class = $this->getConfigProperty('class');
        $Item = new $item_class();
        return $Item;
    }

    /**
     * Validate the request & form fields
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
            [$field_rules, $field_messages] = $Field->validate($field_name, $field_details);

            if (in_array($field_name, $fillable_fields)) {
                $Column = TableHelper::getTableColumn($Item->getTable(), $field_name);
                if (!$Column->getNullable() && $Column->getDefault() == null) {
                    $field_rules[] = 'required';
                }
            }

            $rules[$field_name] = $field_rules;
            $messages[$field_name] = $field_messages;
        }

        if ($this->configClassHasMethod('getStoreValidation')) {
            [$rules, $messages] = $this->Config->getMethod('getStoreValidation')->invoke(null, $rules, $messages, $Item);
        }

        $Validator = Validator::make(request()->all(), $rules, $messages);

        foreach ($this->fields as $field_name => $field_details) {
            $Field = new CrudField($this->CrudInterface, $field_details['type']);
            $Field->validateAfter($Item, $field_name, $field_details);
        }

        return $Validator;
    }

    /**
     * Assign primary key values to the new item
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @return void
     */
    protected function assignPrimaryKeyValues(Model $Item): void
    {
        // If auto-incrementing, we don't need to set anything
        if ($Item->getIncrementing()) { return; }

        foreach ((array)$Item->getKeyName() as $pk_part) {
            switch ($pk_part) {
                case 'language_id': data_set($Item, 'language_id', $this->CrudInterface->getLanguageId()); break;
                case 'version': data_set($Item, 'version', 1); break;
                default:
                    $LastRecord = DB::table($Item->getTable())
                        ->orderBy($pk_part, 'desc')
                        ->first();
                    $new_id = $LastRecord ? $LastRecord->{$pk_part} + 1 : 1;
                    data_set($Item, $pk_part, $new_id);
                    break;
            }
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
