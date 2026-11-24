@php
    $value = data_get($Item, $field_name);
    if (isset($field_details['options']) && !empty($field_details['options'])) {
        foreach ($field_details['options'] as $val => $label) {
            if ($val == $value) {
                $value = $label;
            }
        }
    }
@endphp
{{ $value }}