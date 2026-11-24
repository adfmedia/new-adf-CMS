<?php

namespace Adm\Crud\Field;

use Adm\Helpers\DateHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DateTime extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $datetime = data_get($Item, $field);
        if (!empty($datetime)) {
            $format = !empty($details['datetime_format']) ? $details['datetime_format'] : 'jS F Y, g:ia';
            data_set($Item, $field, date($format, strtotime($datetime)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForExport(Model $Item, string $field, array $details): void
    {
        $datetime = data_get($Item, $field);
        if (!empty($datetime)) {
            $format = 'jS F Y, g:ia';
            if (!empty($details['datetime_format'])) { $format = $details['datetime_format']; }
            if (!empty($details['export_datetime_format'])) { $format = $details['export_datetime_format']; }
            
            data_set($Item, $field, date($format, strtotime($datetime)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        DateHelper::multiFormatDateQuery($query, $table, $field, $search_term);
    }
}