@php
    $storage_key = str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $field_name);
    $selected = old($field_name, data_get($Item, $storage_key));
    if (is_null($selected)) {
        $selected = [];
    }

    $has_move_all = isset($field_details['include_move_all']) && $field_details['include_move_all'];

    $related_class = null;
    $config_details = [];
    if (isset($field_details['relation']['to'])) {
        $related_class = $field_details['relation']['to'];
        if (isset($field_details['relation']['id_field'])) {
            $config_details['id'] = $field_details['relation']['id_field'];
        }
        if (isset($field_details['relation']['title_field'])) {
            $config_details['title'] = $field_details['relation']['title_field'];
        }
    }
@endphp
<div 
    data-orderable-select="os-field-{{ uniqid() }}" 
    data-field="{{ $field_name . '[]' }}"
    {!! !empty($related_class) ? ' data-related="' . $related_class . '" data-config="' . json_encode($config_details) . '"' : '' !!}
    {!! data_get($field_details, 'relation.lazy_load') ? ' data-lazy data-selected="' . json_encode($selected) . '"' : '' !!}
    class="orderable-select"
>
    <div data-unselected class="options-col">
        <div class="options-list-heading search{{ $has_move_all ? ' has-move-all' : '' }}">
            <input data-option-search class="input" placeholder="Search options..." />
            @if( $has_move_all )
                <a
                    data-move-all="unselected"
                    href="#"
                    class="move-all-btn"
                >Select all</a>
            @endif
        </div>
        <div data-unselected-list class="options-list">
            @if(data_get($field_details, 'relation.lazy_load'))
                <p data-search-placeholder class="text-sm text-gray-600 px-2 py-2">Search for options to begin...</p>
            @else
                @foreach( $field_details['options'] as $value => $label )
                    @continue(in_array($value, $selected))

                    <div data-value="{{ $value }}" class="option">
                        <div class="label">{{ $label }}</div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    <div data-selected class="options-col">
        <div class="options-list-heading{{ $has_move_all ? ' has-move-all' : '' }}">
            <span>Selected</span>
            @if( $has_move_all )
                <a
                    data-move-all="selected"
                    href="#"
                    class="move-all-btn"
                >Deselect all</a>
            @endif
        </div>
        <div data-selected-list class="options-list selected">
            @foreach( $selected as $selected_val )
                @continue( !isset($field_details['options'][$selected_val]))

                <div data-value="{{ $selected_val }}" class="option">
                    {{ html()->hidden($field_name . '[]', $selected_val) }}
                    <div class="label">{{ $field_details['options'][$selected_val] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>