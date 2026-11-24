@php
    $field_key = $field_name;
    $storage_key = str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $field_key);
    $rows = old($field_name, data_get($Item, $storage_key));
    $row_count = is_null($rows) || empty($rows) ? 1 : sizeof($rows);

    $singular_label = strtolower(\Illuminate\Support\Str::singular($field_details['label']));
    $add_label = isset($field_details['add_label']) ? $field_details['add_label'] : 'Add ' . $singular_label;
    $remove_label = isset($field_details['remove_label']) ? $field_details['remove_label'] : 'Remove ' . $singular_label;
@endphp
<div
    data-repeatable-field="{{ $field_name }}"
    class="border border-gray-300"
>
    {{-- Inner rows --}}
    <div data-row-wrap>
        @for( $i = 0; $i < $row_count; $i++ )
            <div data-row data-idx="{{ $i }}" class="flex items-stretch border-b border-gray-300">
                {{-- Drag handle --}}
                <div class="w-12 border-r border-gray-300 flex justify-center pt-4">
                    <svg data-handle xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 cursor-grab">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                    </svg>
                </div>
                {{-- Fields --}}
                <div class="w-full flex flex-col">
                    @foreach( $field_details['fields'] as $sub_field_name => $sub_field_details )
                        @php 
                            $sub_field = $field_name . '[' . $i . '][' . $sub_field_name . ']';
                            $sub_field_dot = $field_name . '.' . $i . '.' . $sub_field_name;
                        @endphp
                        <div class="p-2{{ !$loop->last ? '  border-b border-gray-300' : '' }}">
                            <label for="{{ $sub_field }}" class="label">{{ $sub_field_details['label'] }}</label>
                            @if( !empty($sub_field_details['help']) )
                                <div class="help-text">{{ $sub_field_details['help'] }}</div>
                            @endif
                            <div class="input-wrap">
                                @if( view()->exists('crud.fields.form.' . $sub_field_details['type']) )
                                    @include('crud.fields.form.' . $sub_field_details['type'], ['field_name' => $sub_field, 'field_details' => $sub_field_details])
                                @else
                                    <div class="text-red-600 font-bold">Field blade missing for {{ $sub_field_details['type'] }}</div>
                                @endif
                            </div>
                            @if( $errors->has($sub_field_dot) )
                                <p class="mt-2 text-sm text-red-600">{{ $errors->first($sub_field_dot) }}</p>
                            @endif
                        </div>
                    @endforeach
                    <div class="p-2 border-t border-gray-300 flex justify-end">
                        <a data-remove-row href="#" class="btn btn--red">{{ $remove_label }}</a>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Footer row --}}
    <div class="bg-gray-50 p-2 flex">
        <a data-add-row href="#" class="btn">{{ $add_label }}</a>
    </div>
</div>