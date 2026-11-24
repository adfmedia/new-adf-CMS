@php
    $values = data_get($Item, $field_name);
    $labels = [];
    if (isset($field_details['options']) && !empty($field_details['options'])) {
        foreach ($field_details['options'] as $val => $label) {
            if (in_array($val, $values)) {
                $labels[] = $label;
            }
        }
    }
@endphp
{{ implode(', ', $labels) }}