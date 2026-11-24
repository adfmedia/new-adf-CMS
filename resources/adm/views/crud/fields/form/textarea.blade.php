@php
    $field_attrs = ['class' => 'input', 'id' => $field_name, 'rows' => 8];
    if ($field_details['required']) {
        $field_attrs['required'] = '';
    }
@endphp
{{ html()->textarea($field_name)->attributes($field_attrs) }}