@php
    $field_attrs['class'] .= ' input relative -z-10';
@endphp

<div data-relation-search-field class="relative z-10">
    <div class="relative mt-2 rounded-md shadow-sm">
        <div data-icon class="hidden pointer-events-none absolute inset-y-0 left-0 flex mt-5 items-center pl-3">
            <svg width="14" height="14" version="1.0" fill="#777" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                <g transform="translate(0 512) scale(.1 -.1)">
                <path d="m1940 5109c-298-33-624-145-870-299-250-156-472-371-634-614-214-320-328-675-343-1066-40-1063 729-1979 1785-2124 144-20 406-21 555-1 287 38 589 148 824 301l82 53 497-517c858-892 796-836 924-837 65 0 82 4 129 30 63 35 105 82 126 144 28 82 17 168-30 238-11 17-286 307-610 644s-599 624-612 639l-22 26 69 92c216 287 354 633 400 1002 15 122 12 361-5 497-118 909-828 1637-1735 1779-118 18-416 26-530 13zm490-554c613-118 1087-579 1221-1188 32-145 37-408 11-562-112-653-619-1156-1270-1261-109-17-348-19-453-4-555 84-1011 452-1214 980-110 284-125 651-39 948 121 417 403 756 792 951 158 80 308 124 527 155 65 9 344-3 425-19z"/>
                </g>
            </svg>
        </div>
        <input data-search class="absolute bg-transparent w-full border-0 input pl-8" type="text"/>
    </div>
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
</div>
@endif