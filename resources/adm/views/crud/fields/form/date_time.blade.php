<input
    type="datetime-local" name="{{ $field_name }}" id="{{ $field_name }}"
    value="{{ old($field_name, data_get($Item, $field_name)) }}"
    class="input"
    @required($field_details['required'])
/>