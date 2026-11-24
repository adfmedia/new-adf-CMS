@php
    $field_attrs = ['class' => 'input', 'id' => $field_name];
    if ($field_details['required']) {
        $field_attrs['required'] = '';
    }
@endphp
{{ html()->select($field_name, $field_details['options'])->attributes($field_attrs) }}