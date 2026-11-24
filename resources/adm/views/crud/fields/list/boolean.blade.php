@php
    $state = data_get($Item, $field);
    $state_label = isset($column['disabled_text']) ? $column['disabled_text'] : 'Disabled';
    if ($state) {
        $state_label = isset($column['enabled_text']) ? $column['enabled_text'] : 'Enabled';
    }
@endphp
<div class="inline-flex items-center">
    <span class="size-3 rounded-full {{ $state ? 'bg-green-500' : 'bg-red-600' }} mr-2"></span>
    {{ $state_label }}
</div>