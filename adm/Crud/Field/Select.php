<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Select extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $selected = data_get($Item, $field);
        $opts = $this->checkGetFieldOptions($details);
        $selected = isset($opts[$selected]) ? $opts[$selected] : '';
        
        data_set($Item, $field, $selected);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForForm(array $details, ?Model $Item): array
    {
        $this->checkGetFieldOptions($details);
        return $details;
    }

    /**
     * Checks the field configuration for options to display
     * in the dropdown, throwing an exception if not found
     * 
     * @param array $field_details
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function checkGetFieldOptions(array $field_details): array
    {
        if (!isset($field_details['options'])) {
            throw new InvalidArgumentException('No options found for field \'' . $field_details['label'] . '\'.');
        }

        return $field_details['options'];
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        $searched_values = array_filter($field_details['options'], function ($entry) use ($search_term) {
            return stripos($entry, $search_term) !== false;
        });

        $query->orWhereIn($table . '.' . $field, array_keys($searched_values));
    }
}