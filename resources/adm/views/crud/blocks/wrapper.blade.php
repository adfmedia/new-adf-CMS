<div
    data-block
    data-idx="{{ $sort_order }}"
    class="block-wrapper{{ isset($is_new) && $is_new ? ' open' : '' }}{{ isset($status) && !$status ? ' inactive' : '' }}"
>
    {{ html()->hidden($field_name . '[class]', $block['class']) }}

    {{-- Header & drag handle --}}
    <div class="block-header">
        {{-- Drag handle --}}
        <div class="drag-handle">
            <svg data-block-handle xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="handle-icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
            </svg>
        </div>
        {{-- Title --}}
        <div data-block-title class="block-title">{{ isset($CommonBlock) && $CommonBlock ? $CommonBlock->title : $block['title'] }}</div>
        {{-- Actions --}}
        <div class="block-actions">
            <a data-common-block-link="{{ route('adm.route', ['section' => 'common-blocks', 'method' => 'edit']) }}" 
            target="_blank"
            class="action {{ !isset($CommonBlock) || !$CommonBlock ? '!hidden' : '' }}"
            href="{{ isset($CommonBlock) && $CommonBlock ? $CommonBlock->url : null }}" 
            title="Edit common block">
                <svg width="14" height="14" version="1.0" fill="#444" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                    <g transform="translate(0 512) scale(.1 -.1)">
                    <path d="m2360 5114c-114-11-328-47-434-75-352-90-701-266-982-495-85-69-360-342-352-349 2-2 80 19 173 46 137 41 216 57 407 84l237 33 102 57 103 57 117-32 117-33 188 9 189 9 66 100c36 55 70 100 75 100s50-6 99-14c81-12 113-11 314 9l224 21 65 71c36 38 105 96 152 128l87 58 128-19c86-13 141-17 168-12l41 8-74 34c-176 79-443 154-665 187-97 15-453 26-545 18zm653-152c42-24 77-46 77-50 0-7-195-157-228-176-9-5-66-14-128-20l-112-12-52 34-52 33 73 46c97 60 333 192 339 190 3-1 40-21 83-45zm-1056-144 53-22 75 24c41 13 76 22 78 19 18-24 77-133 74-136-3-2-81-24-173-48l-169-44-166 94 32 25c17 14 55 44 83 67s53 42 56 42c2 1 28-9 57-21z"/>
                    <path d="m3120 4547v-52l101-100c118-117 239-251 239-265 0-5-27-23-60-40-60-30-61-30-112-15-28 8-87 29-130 47l-78 32v67 66l-52 21c-29 11-76 29-104 40l-51 21-34-150-34-149-99-22c-54-12-100-28-103-35s-1-45 4-85l8-73 135-26c74-14 136-26 137-25s12 60 24 131c12 72 23 131 24 133 1 1 51-5 111-14 80-12 123-24 160-45 46-26 61-29 138-29h86l56-112c53-106 65-121 211-272l154-160-6-41c-14-86-3-82-160-61l-100 13-211-106-212-106-154-185c-154-185-155-186-174-265l-20-80-47-3c-40-3-66 5-158 47l-110 50-87-43c-48-23-93-45-99-49-8-5-6-28 7-79 10-40 21-86 25-103l7-30 86 100 77 3 77 4-7-95-7-96 62-18c56-15 68-24 127-89l64-71 101 29 100 29 107-16c58-9 174-34 257-56 83-21 179-42 214-46 37-3 70-13 79-22s62-69 119-133l103-116 219-129c121-70 219-133 218-138-2-6-66-131-143-277l-139-267-151-69-150-69-118-314-210-142c-116-78-214-145-220-148-12-9-56-163-47-167 12-5 185 47 295 89 877 331 1510 1126 1630 2049 66 505-12 989-231 1449-65 135-169 311-184 311-5 0-20-4-33-9s-111-17-218-26-203-20-214-25c-11-4-45-46-74-94-30-47-55-86-56-86s-21 7-45 15c-28 10-92 60-198 156l-157 141-41 117c-71 201-70 199-179 297l-100 90-110 22c-60 12-116 22-122 22-9 0-13-17-13-53z"/>
                    <path d="m455 3875c-27-7-62-16-77-19-30-7-74-81-152-254-243-541-290-1148-131-1725 83-305 229-600 428-866 93-124 365-396 490-490 513-383 1142-567 1747-510 63 6 116 12 118 14 2 1-3 70-10 153l-12 151 116 480-57 203c-54 192-62 213-152 380l-94 176 80 169c45 93 81 173 81 179s-35 21-77 34c-75 21-82 26-178 121l-100 98-212 47-211 48-71 145-71 146v-88-88l-32 3c-29 3-48 25-215 251l-183 248v204 205l-131 213c-72 116-133 215-137 218-4 4-101-11-217-32-180-33-231-39-353-39h-143l-70 45c-38 24-69 48-69 52 1 5 40 38 87 76 89 70 90 73 8 52z"/>
                    </g>
                </svg>
            </a>

            <a data-remove-block href="#" class="action remove" title="Remove block">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="action-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </a>
            <a data-toggle-status href="#" class="action status" title="Toggle block status">
                {{ html()->hidden($field_name . '[status]', (isset($is_new) && $is_new ? 1 : null))->attribute('data-status-input', '') }}
                <span data-status-dot class="status-dot"></span>
                <span data-status-label class="status-label"></span>
            </a>
            @if( !empty($fields) )
                <a data-collapse href="#" class="action expnd-cllps" title="Expand or collapse block">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="action-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
    @if( !empty($fields) )
        <div class="block-content p-4 pt-2">
            <div class="divide-y divide-gray-200 space-y-3">
                @foreach( $fields as $field_name => $field_details )
                    <div class="field-wrap">
                        <label for="{{ $field_prefix . '[' . $field_name . ']' }}" class="label">{{ $field_details['label'] }}</label>
                        @if( !empty($field_details['help']) )
                            <div class="help-text">{{ $field_details['help'] }}</div>
                        @endif
                        <div class="input-wrap">
                            @if( view()->exists('crud.fields.form.' . $field_details['type']) )
                                @include(
                                    'crud.fields.form.' . $field_details['type'],
                                    ['field_name' => $field_prefix . '[' . $field_name . ']']
                                )
                            @else
                                <div class="text-red-600 font-bold">Field blade missing for {{ $field_details['type'] }}</div>
                            @endif
                        </div>
                        @if( $errors->has($field_name) )
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first($field_name) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>