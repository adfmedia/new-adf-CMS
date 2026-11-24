<?php

namespace Adm\Crud\Field;

use Adm\Helpers\DateHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Date extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $date = data_get($Item, $field);
        if (!empty($date)) {
            $format = !empty($details['date_format']) ? $details['date_format'] : 'jS F Y';
            data_set($Item, $field, date($format, strtotime($date)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForExport(Model $Item, string $field, array $details): void
    {
        $date = data_get($Item, $field);
        if (!empty($date)) {
            $format = 'jS F Y';
            if (!empty($details['date_format'])) { $format = $details['date_format']; }
            if (!empty($details['export_date_format'])) { $format = $details['export_date_format']; }
            
            data_set($Item, $field, date($format, strtotime($date)));
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
