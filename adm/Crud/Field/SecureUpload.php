<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class SecureUpload extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
        if (!isset($details['storage']) || empty($details['storage'])) {
            throw new InvalidArgumentException('The configuration for "' . $field . '" is missing a \'storage\' directive');
        }
        if (empty($details['storage']['directory'])) {
            throw new InvalidArgumentException('The configuration for "' . $field . '" is missing a storage directory');
        }

        $preview = null;
        $path = data_get($Item, $field);
        if (!empty($path) && isset($details['storage']['link_route']) && !empty($details['storage']['link_route'])) {
            $preview = route($details['storage']['link_route'], $path);
        }

        $form_details = [
            'path'      => $path,
            'preview'   => $preview,
        ];
        data_set($Item, $field, $form_details);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        if (request()->hasFile($field . '.file') && request()->file($field . '.file')->isValid()) {
            $path = request()->file($field . '.file')->store($details['storage']['directory']);
            $path = str_replace($details['storage']['directory'] . '/', '', $path);
            data_set($Item, $field, $path);
        }
    }
}