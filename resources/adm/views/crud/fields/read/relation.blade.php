@php
    $value = data_get($Item, $field_name);
    if (isset($field_details['relation']['options']) && !empty($field_details['relation']['options'])) {
        foreach ($field_details['relation']['options'] as $val => $label) {
            if ($val == $value) {
                $value = $label;
            }
        }
    }
@endphp
{{ $value }}