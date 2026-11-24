@php
    $format = !empty($details['date_format']) ? $details['date_format'] : 'jS F Y';
    $value = data_get($Item, $field_name);
@endphp
{{ $value ? date($format, strtotime($value)) : '' }}