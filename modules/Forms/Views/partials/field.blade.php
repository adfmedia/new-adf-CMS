<div 
    data-field="{{ $field->form_field_name }}"
    class="field-wrap"
>
    @if( in_array($field->type, ['checkbox', 'radio']) )
        <p class="label">{{ $field->label }}</p>
    @else
        <label for="{{ $field->form_field_id }}" class="label">{{ $field->label }}</label>
    @endif
    @if( !empty($field->help_text) )
        <div class="help-text">{{ $field->help_text }}</div>
    @endif

    <div class="input-wrap">
        @switch( $field->type )
            @case('text')
                <input 
                    type="text" 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    placeholder="{{ $field->placeholder }}" 
                    class="input" 
                    @required($field->required) 
                >
                @break
            @case('email')
                <input 
                    type="email" 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    placeholder="{{ $field->placeholder }}" 
                    class="input" 
                    @required($field->required) 
                >
                @break
            @case('tel')
                <input 
                    type="tel" 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    placeholder="{{ $field->placeholder }}" 
                    class="input" 
                    @required($field->required) 
                >
                @break
            @case('number')
                <input 
                    type="number" 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    placeholder="{{ $field->placeholder }}" 
                    class="input" 
                    @required($field->required) 
                >
                @break
            @case('date')
                <input 
                    type="date" 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    class="input" 
                    @required($field->required) 
                >
                @break
            @case('textarea')
                <textarea 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    placeholder="{{ $field->placeholder }}" 
                    {!! !empty($field->settings['rows']) ? ' rows="' . $field->settings['rows'] . '"' : '' !!}
                    class="input field-sizing-content min-h-32" 
                    @required($field->required)
                ></textarea>
                @break
            @case('select')
                <select 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    class="input" 
                    @required($field->required)
                >
                    @if( !empty($field->placeholder) )
                        <option value="">{{ $field->placeholder }}</option>
                    @endif
                    @foreach( $field->options_array as $option )
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @break
            @case('checkbox')
                <div class="flex flex-wrap items-center space-x-4">
                    @foreach( $field->options_array as $option )
                        <label class="checkbox-wrap inline-flex items-center space-x-2 py-1 mb-1">
                            <input 
                                type="checkbox" name="{{ $field->form_field_name . '[]' }}" value="{{ $option }}" @required($field->required) 
                                class="size-6"
                            >
                            <span class="checkbox-label">{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
                @break
            @case('radio')
                <div class="flex flex-wrap items-center space-x-4">
                    @foreach( $field->options_array as $option )
                        <label class="radio-wrap inline-flex items-center space-x-2 py-1 mb-1">
                            <input 
                                type="radio" name="{{ $field->form_field_name }}" value="{{ $option }}" @required($field->required) 
                                class="size-6"
                            >
                            <span class="radio-label">{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
                @break
            @case('file')
                <input 
                    type="file" 
                    name="{{ $field->form_field_name }}" 
                    id="{{ $field->form_field_id }}" 
                    class="input" 
                    @required($field->required) 
                >
                @break
        @endswitch
    </div>
</div>