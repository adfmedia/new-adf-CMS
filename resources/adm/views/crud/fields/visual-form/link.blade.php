@php
    $field_attrs = ['data-link-input' => '', 'class' => 'input with-addon-button', 'id' => $field_name, 'autocomplete' => 'off'];
    if ($field_details['required']) {
        $field_attrs['required'] = '';
    }
@endphp
<div data-link-field class="relative">
    <div class="flex rounded-md">
        <div class="relative flex grow items-stretch focus-within:z-10">
            {{ html()->text($field_name . '[value]')->attributes($field_attrs) }}
        </div>
        <a data-link-browse href="#" class="addon-button hidden">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </a>
    </div>
    <div 
        data-lookup-results-wrap
        class="hidden absolute w-full top-full left-0 h-40 mt-1 p-4 pb-6 bg-white shadow-lg rounded-md border border-gray-300"
    >
        <div data-loader class="absolute inset-0 flex items-center p-4 text-primary-medium">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-3 animate-spin">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span class="font-semibold">Searching...</span>
        </div>
        <div data-lookup-results class="h-full overflow-y-scroll pr-4 mb-1"></div>
        <div class="flex justify-end">
            <a data-close-results href="#" class="text-xs text-primary-medium hover:underline">Close</a>
        </div>
    </div>
</div>