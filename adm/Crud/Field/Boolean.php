<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Boolean extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        $value = !empty($value);
        data_set($Item, $field, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        $boolean_term = $this->getBooleanValueSearchTerm($search_term, $field_details);
        if(!is_null($boolean_term)) {
            $query->orWhere($table . '.' . $field, $boolean_term);
        }
    }

    /**
     * Returns a true or false boolean after attempting to match
     * against common yes/no type phrases - or null if no match
     * found
     * 
     * @param string $search_term
     * @param array $field_details
     * @return ?bool
     */
    protected function getBooleanValueSearchTerm(string $search_term, array $field_details): ?bool
    {
        $search_term = strtolower($search_term);
        if (isset($field_details['enabled_text']) && $search_term == strtolower($field_details['enabled_text'])) { return true; }
        if (isset($field_details['disabled_text']) && $search_term == strtolower($field_details['disabled_text'])) { return false; }

        $truths = [
            'yes', 'on', 'active', 'enabled'
        ];
        $falses = [
            'no', 'off', 'inactive', 'disabled'
        ];
        if (in_array($search_term, $truths)) { return true; }
        if (in_array($search_term, $falses)) { return false; }

        return null;
    }
}