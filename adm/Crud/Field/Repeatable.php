<?php

namespace Adm\Crud\Field;

use Adm\Crud\CrudField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Repeatable extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $sortable = false;

    /**
     * {@inheritdoc}
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
        $this->checkForRepeatableFields($details);
        foreach ($details['fields'] as $sub_field => $sub_details) {
            $Field = new CrudField($this->CrudInterface, $sub_details['type']);
            $Field->prepareForForm($Item, $field . '.' . $sub_field, $sub_details);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForForm(array $details, ?Model $Item): array
    {
        $this->checkForRepeatableFields($details);
        foreach ($details['fields'] as $sub_field => $sub_details) {
            $Field = new CrudField($this->CrudInterface, $sub_details['type']);
            $sub_details['required'] = $this->isFieldRequired($sub_details);
            $details['fields'][$sub_field] = $Field->modifyFieldDetailsForForm($sub_details, $Item);
        }
        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        $item_data = data_get($Item, $field);
        if (is_null($item_data)) { $item_data = []; }

        foreach ($details['fields'] as $sub_field => $sub_details) {
            $Field = new CrudField($this->CrudInterface, $sub_details['type']);

            foreach ($value as $idx => $row) {
                if (!isset($item_data[$idx])) { $item_data[$idx] = []; }

                $sub_value = isset($row[$sub_field]) ? $row[$sub_field] : null;
                $item_data[$idx][$sub_field] = $sub_value;

                $Field->prepareForStorage($item_data, $idx . '.' . $sub_field, $sub_value, $sub_details);
            }
        }

        // Clear pre-stored data when saving fewer elements
        if (sizeof($item_data) > sizeof($value)) {
            for ($i = sizeof($value); $i <= sizeof($item_data); $i++) {
                unset($item_data[$i]);
            }
        }
        
        data_set($Item, $field, $item_data);
    }

    /**
     * {@inheritdoc}
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        foreach ($details['fields'] as $sub_field => $sub_details) {
            $Field = new CrudField($this->CrudInterface, $sub_details['type']);

            foreach ($value as $idx => $row) {
                $sub_value = isset($row[$sub_field]) ? $row[$sub_field] : null;
                $Field->afterStored($Item, $field . '.' . $idx . '.' . $sub_field, $sub_value, $sub_details);
            }
        }
    }

    /**
     * Checks the field configuration for details about the 
     * repeatable fields, throwing an exception if not found
     * 
     * @param array $field_details
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function checkForRepeatableFields(array $field_details): void
    {
        if (!isset($field_details['fields'])) {
            throw new InvalidArgumentException('No repeatable fields found for field \'' . $field_details['label'] . '\'.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        // Do nothing
    }
}