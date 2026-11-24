@php
    $format = !empty($details['datetime_format']) ? $details['datetime_format'] : 'jS F Y, g:ia';
    $value = data_get($Item, $field_name);
@endphp
{{ $value ? date($format, strtotime($value)) : '' }}