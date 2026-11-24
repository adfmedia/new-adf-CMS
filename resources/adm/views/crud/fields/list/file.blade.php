@php
    $stored_file = data_get($Item, $field);
@endphp
@if( !empty($stored_file) && !empty($stored_file['file_path']) )
<a href="{{ $stored_file['file_path'] }}" target="_blank" class="text-primary-medium hover:underline">{{ $stored_file['file_path'] }}</a>
@endif