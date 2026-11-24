@php
    $config_details = [];
    if (isset($field_details['relation']['id_field'])) {
        $config_details['id'] = $field_details['relation']['id_field'];
    }
    if (isset($field_details['relation']['title_field'])) {
        $config_details['title'] = $field_details['relation']['title_field'];
    }
    $field_attrs = [
        'class' => 'input', 'id' => $field_name,
        'data-relation-field'   => $field_details['relation']['to'],
        'data-relation-config'  => json_encode($config_details)
    ];
    if ($field_details['required']) {
        $field_attrs['required'] = '';
    }
@endphp

@if(isset($field_details['searchable']) && $field_details['searchable'])
    @include('crud.fields.form.partials.relation-search')
@else
    @include('crud.fields.form.partials.relation-plain')
@endif