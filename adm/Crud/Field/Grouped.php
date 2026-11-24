<?php

namespace Adm\Crud\Field;

use Adm\Crud\CrudField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Grouped extends Field
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
        $this->checkForGroupedFields($details);
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
        $this->checkForGroupedFields($details);
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
        if (is_null($value)) { $value = []; }

        foreach ($details['fields'] as $sub_field => $sub_details) {
            $Field = new CrudField($this->CrudInterface, $sub_details['type']);

            foreach ($value as $idx => $row) {
                if (!isset($item_data[$idx])) { $item_data[$idx] = []; }

                $sub_value = isset($row[$sub_field]) ? $row[$sub_field] : null;
                $item_data[$idx][$sub_field] = $sub_value;

                $Field->prepareForStorage($item_data, $idx . '.' . $sub_field, $sub_value, $sub_details);
            }
        }
        data_set($Item, $field, $item_data);
    }

    /**
     * {@inheritdoc}
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        if (is_null($value)) { $value = []; }
        
        foreach ($details['fields'] as $sub_field => $sub_details) {
            $Field = new CrudField($this->CrudInterface, $sub_details['type']);

            foreach ($value as $idx => $row) {
                $sub_value = isset($row[$sub_field]) ? $row[$sub_field] : null;
                $Field->afterStored($Item, $field . '.' . $idx, $row, $sub_details);
            }
        }
    }

    /**
     * Checks if the field configuration include information
     * about the fields to be grouped - throws an exception 
     * if not found
     * 
     * @param array $field_details
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function checkForGroupedFields(array $field_details): void
    {
        if (!isset($field_details['fields'])) {
            throw new InvalidArgumentException('No grouped fields found for field \'' . $field_details['label'] . '\'.');
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