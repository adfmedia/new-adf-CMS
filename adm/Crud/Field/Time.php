<?php

namespace Adm\Crud\Field;

use Adm\Helpers\DateHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Time extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $time = data_get($Item, $field);
        if (!empty($time)) {
            $format = !empty($details['time_format']) ? $details['time_format'] : 'g:ia';
            data_set($Item, $field, date($format, strtotime($time)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForExport(Model $Item, string $field, array $details): void
    {
        $time = data_get($Item, $field);
        if (!empty($time)) {
            $format = 'g:ia';
            if (!empty($details['time_format'])) { $format = $details['time_format']; }
            if (!empty($details['export_time_format'])) { $format = $details['export_time_format']; }
            
            data_set($Item, $field, date($format, strtotime($time)));
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
