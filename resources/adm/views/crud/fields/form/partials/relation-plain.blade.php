{{ html()->select($field_name, $field_details['relation']['options'])->attributes($field_attrs) }}
@if( isset($field_details['relation']['create_link']) )
<div class="flex justify-end mt-2">
    <a 
        href="{{ $field_details['relation']['create_link']['url'] }}" 
        target="_blank"
        class="text-xs text-primary-medium hover:underline"
    >
        {{ $field_details['relation']['create_link']['text'] }}
    </a>
</div>
@endif