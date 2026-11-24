@php
    $disabled_text = data_get($field_details, 'disabled_text', 'Disabled');
    $enabled_text = data_get($field_details, 'enabled_text', 'Enabled');

    $is_enabled = data_get($Item, $field_name);
@endphp

<div class="text-sm font-semibold flex items-center {{ $is_enabled ? 'text-green-600' : 'text-red-600' }}">
    <span class="block size-3 rounded-full {{ $is_enabled ? 'bg-green-500' : 'bg-red-600' }} mr-2"></span>

    {{ $is_enabled ? $enabled_text : $disabled_text }}
</div>