@php
    $field_attrs = ['class' => 'input', 'id' => $field_name];
    if ($field_details['required']) {
        $field_attrs['required'] = '';
    }
@endphp
{{ html()->input('date', $field_name)->attributes($field_attrs) }}