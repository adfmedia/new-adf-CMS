<?php

namespace Adm\Crud\Field;

use Adm\Crud\CrudInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class Field
{
    /**
     * Instance of the CrudInterface for use in field processing
     * 
     * @var \Adm\Crud\CrudInterface
     */
    protected $CrudInterface;

    /**
     * Settings for the field being processed
     * 
     * @var array
     */
    protected $field_settings;

    /**
     * The current item being processed, or null
     * 
     * @var ?\Illuminate\Database\Eloquent\Model
     */
    protected $Item = null;

    /**
     * Whether this field type is sortable or not
     * 
     * @var bool
     */
    protected $sortable = true;

    /**
     * Construct a new field instance, including the CRUD
     * interface
     * 
     * @param CrudInterface $CrudInterface  The current CRUD interface
     */
    public function __construct(CrudInterface $CrudInterface)
    {
        $this->CrudInterface = $CrudInterface;
    }

    /**
     * Prepare an item's field for display in the list view
     * 
     * @param Model $Item       The item being displayed
     * @param string $field     The field to display
     * @param array $details    Any assigned field details
     * @return void
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
    }

    /**
     * Allows modification of a field's details for list views
     * 
     * @param array $details    The field details to modify
     * @return array
     */
    public function modifyFieldDetailsForListing(array $details): array
    {
        return $details;
    }

    /**
     * Prepare an item's field for export data
     * 
     * @param Model $Item       The item being exported
     * @param string $field     The field being exported
     * @param array $details    Any assigned field details
     * @return void
     */
    public function prepareForExport(Model $Item, string $field, array $details): void
    {
        $this->prepareForListing($Item, $field, $details);
    }

    /**
     * Prepare an item's field for display in the form view
     * 
     * @param Model|array $Item     The item being prepared
     * @param string $field         The field being prepared
     * @param array $details        Any assigned field details
     * @return void
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
    }

    /**
     * Allows modification of a field's details for form views
     * 
     * @param array $details        Any assigned field details
     * @param ?Model $Item          The item being displayed
     * @return array
     */
    public function modifyFieldDetailsForForm(array $details, ?Model $Item): array
    {
        return $details;
    }

    /**
     * Prepare an item's field for display in the read view
     * 
     * @param Model $Item       The item being displayed
     * @param string $field     The field being displayed
     * @param array $details    Any assigned field details
     * @return void
     */
    public function prepareForRead(Model $Item, string $field, array $details): void
    {
    }

    /**
     * Prepare an item's field value ready for storage in the database
     * 
     * @param Model|array $Item     The item being stored
     * @param string $field         The field being stored
     * @param mixed $value          The value POSTed for storage
     * @param array $details        Any assigned field details
     * @return void
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        data_set($Item, $field, $value);
    }

    /**
     * Allows further manipulation of an item after storage
     * in the database
     * 
     * @param Model $Item       The item being stored
     * @param string $field     The field being stored
     * @param mixed $value      The value POSTed for storage
     * @param array $details    Any assigned field details
     * @return void
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
    }

    /**
     * Field specific function allowing certain field types
     * to bypass running the afterStored function above
     * when being called by the preview handler
     * 
     * @return bool
     */
    public function runAfterStoredInPreviewHandler(): bool
    {
        return true;
    }

    /**
     * Allows even further manipulation of an item after storage
     * in the database, called after all other field's afterStored
     * method is called
     * 
     * @param Model $Item       The item being stored
     * @param string $field     The field being stored
     * @param mixed $value      The value POSTed for storage
     * @param array $details    Any assigned field details
     * @return void
     */
    public function postAfterStored(Model $Item, string $field, mixed $value, array $details): void
    {
    }

    /**
     * Allows further manipulation of an item after deletion
     * 
     * @param Model $Item       The item being deleted
     * @param string $field     The current field name
     * @param array $details    Any assigned field details
     * @return void
     */
    public function afterDeleted(Model $Item, string $field, array $details): void
    {
    }

    /**
     * Adds validation rules & messages for the field
     * 
     * @param string $field     The field being validated
     * @param array $details    Any assigned field details
     * @param ?Model $Item      The item being validated
     * @param bool $is_update   Whether the validation is for an update or create
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(string $field, array $details, ?Model $Item = null, bool $is_update = false): array
    {
        $rules      = [];
        $messages   = [];
        if (!empty($details['validation'])) {
            $rules = $details['validation']['rules'];
            $messages = $details['validation']['messages'];

            if ($is_update && isset($details['validation']['update_rules'])) {
                $rules = $details['validation']['update_rules'];
            }
        }

        return [$rules, $messages];
    }

    /**
     * Perform any additional validation after the first round
     * has completed
     * 
     * @param Model $Item       The item being validated
     * @param string $field     The field being validated
     * @param array $details    Any additional field details
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateAfter(Model $Item, string $field, array $details): void
    {
        // Do nothing by default!
    }

    /**
     * Add a search condition to a query for this field for
     * the list view
     * 
     * @param Builder $query            The current list query
     * @param string $table             The database table being queried
     * @param string $field             The field being queried
     * @param string $search_term       The search term used
     * @param array $field_details      Any assigned field details
     * @return void
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        $query->orWhere($table . '.' . $field, 'LIKE', '%' . $search_term . '%');
    }

    /**
     * Apply sort flags to the provided column based on what's
     * being sorted by in list views
     * 
     * @param string $field_name        The field name of the column being displayed
     * @param array $column             The column being displayed in the list
     * @param string $current_url       The current URL of the list
     * @return array
     */
    public function setSortFields(string $field_name, array $column, string $current_url): array
    {
        if ($this->sortable) {
            $column['sortable'] = true;
            $column['is_sorted'] = $this->CrudInterface->getItemSortField() == $field_name;
            $column['sort_dir'] = null;
            if ($column['is_sorted']) {
                $column['sort_dir'] = $this->CrudInterface->getItemSortDirection() == 'desc' ? 'desc' : 'asc';
            }

            $dir = 'asc';
            if ($column['is_sorted'] && $this->CrudInterface->getItemSortDirection() == 'asc') {
                $dir = 'desc';
            }
            $column['sort_url'] = $current_url . '?sort=' . $field_name . '&sortdir=' . $dir . (request()->query('search') ? '&search=' . request()->query('search') : '');
        }
        return $column;
    }

    /**
     * Add a sort order to a query for the list view
     * 
     * @param Builder $query            The current list query
     * @param string $table             The database table being queried
     * @param string $field_name        The field being sorted by
     * @param string $sort_dir          The sort direction
     * @param array $field_details      Any assigned field details
     * @return void
     */
    public function sort(Builder $query, string $table, string $field_name, string $sort_dir, array $field_details): void
    {
        $query->orderBy($table . '.' . $field_name, $sort_dir);
    }

    /**
     * Return boolean indicating whether a field is required
     * based on it's validation rules
     * 
     * @param array $field_details  The configuration class details for the field
     * @return bool
     */
    protected function isFieldRequired(array $field_details): bool
    {
        return in_array('required', (array)data_get($field_details, 'validation.rules'));
    }
}