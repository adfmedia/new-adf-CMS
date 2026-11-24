@php $selected = html()->value($field_name, data_get($Item, $field_name)); @endphp
<div 
    data-file-field 
    data-file="{{ !empty($selected) && !empty($selected['file_id']) ? $selected['file_id'] : '' }}"
    data-folder="{{ !empty($selected) && !empty($selected['folder']) ? json_encode($stored_data['folder']) : '' }}"
    class="relative"
>
    @php
        $field_attrs = ['data-file-input' => ''];
        if ($field_details['required']) {
            $field_attrs['required'] = '';
        }
    @endphp
    {{ html()->hidden($field_name . '[file_id]')->attributes($field_attrs) }}
    <div class="flex rounded-md">
        <div class="relative flex grow items-stretch focus-within:z-10">
            {{ html()->text($field_name . '[file_path]')->attributes(['data-file-path-input' => '', 'id' => $field_name, 'autocomplete' => 'off', 'readonly' => true, 'class' => 'input with-addon-button']) }}
        </div>
        <a data-remove-btn href="#" class="addon-button no-rounding">
            <span class="text-red-600">Remove</a>
        </a>
        <a data-file-browse href="#" class="addon-button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            Browse
        </a>
    </div>
</div>