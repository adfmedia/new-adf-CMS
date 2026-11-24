<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FormStructure extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $sortable = false;

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        // Don't populate on initial store as item ID needed
    }

    /**
     * {@inheritdoc}
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        data_set($Item, $field, $value);
        $Item->save();
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        // Do nothing
    }
}