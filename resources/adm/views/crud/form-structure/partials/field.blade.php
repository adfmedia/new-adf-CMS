@php
    $field_type_opts    = [
        'text'      => 'Text',
        'email'     => 'Email address',
        'tel'       => 'Telephone number',
        'textarea'  => 'Multi-line text',
        'select'    => 'Dropdown choice',
        'checkbox'  => 'Checkbox choice',
        'radio'     => 'Radio button choice',
        'number'    => 'Number',
        'date'      => 'Date',
        'file'      => 'File upload'
    ];

    $idx = 'PLACEHOLDER';
    $label = 'New field';
    $type = 'Text';

    if (isset($field_idx)) {
        $idx = $field_name . '[rows][' . $row_idx . '][columns][' . $col_idx . '][fields][' . $field_idx . ']';
        $label = !empty($field) && !empty($field['label']) ? $field['label'] : '';
        $type = !empty($field) && !empty($field['type']) ? $field['type'] : '';
        if (isset($field_type_opts[$type])) {
            $type = $field_type_opts[$type];
        }
    }
@endphp
<div 
    data-form-field
    data-idx="{{ $idx }}"
    class="structure-field"
>
    @if( isset($field) )
        {{ html()->hidden($idx . '[id]') }}
    @endif
    <div class="field-header">
        <div data-field-handle class="handle">
            <svg class="handle-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
            </svg>
        </div>
        <div class="title">
            <span data-field-label class="field-label">{{ $label }}</span>
            <span data-field-type class="field-type">{{ $type }}</span>
        </div>
        <button data-field-toggle class="toggle-btn">
            <svg class="toggle-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
    <div class="field-inner">
        <div class="field-wrap">
            <label for="{{ $idx }}[label]" class="label">Field label</label>
            <div class="input-wrap">
                {{ html()->text($idx . '[label]')->attributes(['data-label-inpt' => '', 'id' => $idx . '[label]', 'class' => 'input']) }}
            </div>
        </div>
        <div class="field-wrap">
            <label for="{{ $idx }}[settings][placeholder]" class="label">Field placeholder</label>
            <div class="input-wrap">
                {{ html()->text($idx . '[settings][placeholder]')->attributes(['id' => $idx . '[settings][placeholder]', 'class' => 'input']) }}
            </div>
        </div>
        <div class="field-wrap">
            <label for="{{ $idx }}[type]" class="label">Field type</label>
            <div class="input-wrap">
                {{ html()->select($idx . '[type]', $field_type_opts)->attributes(['data-type-inpt' => '', 'id' => $idx . '[type]', 'class' => 'input']) }}
            </div>
        </div>
        <div 
            data-field-options data-option-types='@json(['select', 'checkbox', 'radio'])'
            class="hidden"
        >
             <div class="field-wrap">
                <label for="{{ $idx }}[options]" class="label">Selection options (one per line)</label>
                <div class="input-wrap">
                    {{ html()->textarea($idx . '[options]')->attributes(['id' => $idx . '[options]', 'class' => 'input', 'rows' => 5]) }}
                </div>
            </div>
        </div>
        <div 
            data-field-optional-settings data-types='@json(['textarea'])'
            class="hidden"
        >
             <div class="field-wrap">
                <label for="{{ $idx }}[settings][rows]" class="label">Number of text rows</label>
                <div class="input-wrap">
                    {{ html()->text($idx . '[settings][rows]')->attributes(['id' => $idx . '[settings][rows]', 'class' => 'input']) }}
                </div>
            </div>
        </div>
        <div data-field-settings class="field-settings">
            <div class="flex items-start mb-2">
                <div class="h-6 flex items-center">
                    <input type="hidden" name="{{ $idx }}[required]" value="0" />
                    {{ html()->checkbox($idx . '[required]', null, 1)->attributes(['id' => $idx . '[required]', 'class' => 'size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium']) }}
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="{{ $idx }}[required]" class="font-medium text-gray-900">Mandatory</label>
                </div>
            </div>
            <div class="flex items-start mb-2">
                <div class="h-6 flex items-center">
                    <input type="hidden" name="{{ $idx }}[settings][show_in_cms_list]" value="0" />
                    {{ html()->checkbox($idx . '[settings][show_in_cms_list]', null, 1)->attributes(['id' => $idx . '[settings][show_in_cms_list]', 'class' => 'size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium']) }}
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="{{ $idx }}[settings][show_in_cms_list]" class="font-medium text-gray-900">Show in CMS list</label>
                </div>
            </div>
        </div>
        <div class="field-wrap">
            <label for="{{ $idx }}[code_reference]" class="label">Code reference</label>
            <div class="input-wrap">
                {{ html()->text($idx . '[code_reference]')->attributes(['id' => $idx . '[code_reference]', 'class' => 'input']) }}
            </div>
        </div>
        <div class="field-wrap">
            <a 
                data-remove-field 
                href="#"
                class="text-sm text-red-600 hover:underline"
            >Remove field</a>
        </div>
    </div>
</div>