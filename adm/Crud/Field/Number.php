<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Model;

class Number extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        data_set($Item, $field, (int)$value);
    }
}