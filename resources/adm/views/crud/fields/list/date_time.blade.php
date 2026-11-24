@php
    $format = !empty($column['datetime_format']) ? $column['datetime_format'] : 'jS F Y, g:ia';
@endphp
{{ date($format, strtotime(data_get($Item, $field))) }}