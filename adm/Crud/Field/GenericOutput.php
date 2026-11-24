<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Model;
use Override;

class GenericOutput extends Field 
{
    /**
     * {@inheritdoc}
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
        if (isset($details['generate_method']) && !empty($details['generate_method']) && is_callable($details['generate_method'])) {
            call_user_func($details['generate_method'], $Item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForRead(Model $Item, string $field, array $details): void
    {
        $this->prepareForForm($Item, $field, $details);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        // Do nothing - output should not be saved
    }
}