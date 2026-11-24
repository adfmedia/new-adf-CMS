@php
    $field_attrs = ['class' => 'input', 'id' => $field_name, 'data-colour-picker' => ''];
    if ($field_details['required']) {
        $field_attrs['required'] = '';
    }
@endphp
{{ html()->text($field_name)->attributes($field_attrs) }}