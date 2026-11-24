@php $selected = html()->value($field_name, data_get($Item, $field_name)); @endphp
@php 
    // Convert array field names to dot notation
    $data_field_name = $field_name;
    if (stripos($data_field_name, '[') !== false) {
        $data_field_name = str_replace([']', '['], ['', '.'], $data_field_name);
    }

    $selected = html()->value(data_get($Item, $data_field_name)); 
@endphp
<div class="flex flex-wrap items-start">
    @foreach( $field_details['options'] as $value => $label )
        <div class="relative inline-flex items-start mr-6">
            <div class="h-6 flex items-center">
                <input
                    type="checkbox" name="{{ $field_name }}[]" id="{{ $field_name . '-' . $value }}"
                    value="{{ $value }}"
                    @checked(!empty($selected) && is_array($selected) && in_array($value, $selected))
                    @required($field_details['required'])
                    class="size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium"
                >
            </div>
            <div class="ml-3 text-sm leading-6">
                <label for="{{ $field_name . '-' . $value }}" class="font-medium text-gray-900">{{ $label }}</label>
            </div>
        </div>
    @endforeach
</div>