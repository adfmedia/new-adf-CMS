@php $stored_details = data_get($Item, $field_name); @endphp
<div data-secure-file-field class="relative">
    @php
        $field_attrs = ['data-secure-file-input' => ''];
        if ($field_details['required']) {
            $field_attrs['required'] = '';
        }
    @endphp
    {{ html()->hidden($field_name . '[path]')->attributes($field_attrs) }}
    @if( !empty($stored_details) && !empty($stored_details['preview']) )
        <div class="text-sm mb-2"><a href="{{ $stored_details['preview'] }}" target="_blank" class="text-primary-medium underline hover:no-underline">Preview the current file</a> or select another below to overwrite it.</div>
    @endif
    <div class="flex rounded-md">
        <div class="relative flex grow items-stretch focus-within:z-10">
            {{ html()->file($field_name . '[file]')->attributes(['data-file-input' => '', 'id' => $field_name . '-file', 'class' => 'input with-addon-button file:hidden pl-2']) }}
        </div>
        <label for="{{ $field_name }}-file" class="addon-button cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            Select file
        </label>
    </div>
</div>