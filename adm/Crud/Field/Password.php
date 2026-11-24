<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Password extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $sortable = false;

    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        data_set($Item, $field, '************');
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
        data_set($Item, $field, '');
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        $pw = $value;
        if (!$pw) { 
            $OldItem = $this->CrudInterface->getOldItem();
            if ($OldItem) {
                data_set($Item, $field, data_get($OldItem, $field));
            }
            return; 
        }

        data_set($Item, $field, Hash::make($pw));
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        // Do nothing
    }
}
