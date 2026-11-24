<div class="image-size-wrap">
    @foreach( $field_details['sizes'] as $size_key => $size_details )
        @php
            $field_key = $field_name . '[' . $size_key . ']';
            $storage_key = str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $field_key);
            $stored_data = data_get($Item, $storage_key);
        @endphp
        <div 
            data-image-tile
            data-size="{{ $size_key }}" data-details="{{ json_encode($size_details) }}"
            class="size-tile"
        >
            {{-- Inputs --}}
            {{ html()->hidden($field_name . '[' . $size_key . '][file_id]')->attribute('data-file-id-input', '') }}
            {{ html()->hidden($field_name . '[' . $size_key . '][crop_w]')->attribute('data-crop-input', 'w') }}
            {{ html()->hidden($field_name . '[' . $size_key . '][crop_h]')->attribute('data-crop-input', 'h') }}
            {{ html()->hidden($field_name . '[' . $size_key . '][crop_x]')->attribute('data-crop-input', 'x') }}
            {{ html()->hidden($field_name . '[' . $size_key . '][crop_y]')->attribute('data-crop-input', 'y') }}

            <div class="title-details">
                <div class="title">{{ $size_details['label'] }}</div>
                <div class="dims">{{ $size_details['dimensions'] }}</div>
            </div>
            <a 
                data-tile-select
                data-file="{{ !empty($stored_data) ? $stored_data['file_id'] : '' }}" 
                data-folder="{{ !empty($stored_data) ? json_encode($stored_data['folder_details']) : '' }}"
                href="#"
                class="image-wrap"
            >
                <div 
                    data-placeholder 
                    class="image-placeholder{{ !empty($stored_data) && !empty($stored_data['file_id']) ? ' hidden' : '' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="placeholder-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    @if( isset($size_details['fallback_label']) )
                        <div class="placeholder-text relative group">
                            Click to select override for <span class="font-semibold">{{ $size_details['fallback_label'] }}</span> image
                            <div class="tooltip bottom-left delay-500 group-hover:opacity-100">
                                <div class="flex">
                                    <svg 
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                        class="shrink-0 w-5 h-5 mr-2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                    </svg>
                                    <span>The website will currently display the {{ $size_details['fallback_label'] }} image, resized to suit the dimensions above.</span>
                                </div>
                                <svg class="tooltip-arrow" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                    <polygon points="0,0 12,12 24,0"/>
                                </svg>
                            </div>
                        </div>
                    @else
                        <div class="placeholder-text">Click to select image</div>
                    @endif
                </div>
                <div data-preview class="image-preview{{ empty($stored_data) || empty($stored_data['file_id']) ? ' hidden' : '' }}">
                    @if( !empty($stored_data) && !empty($stored_data['file_id']) && !empty($stored_data['preview_image']) )
                        <img
                            src="{{ $stored_data['preview_image']['src'] }}"
                            alt="{{ $stored_data['alt'] }}"
                            width="{{ $stored_data['preview_image']['width'] }}"
                            height="{{ $stored_data['preview_image']['height'] }}"
                            loading="lazy"
                        />
                    @else
                        {{-- 1px square blank image --}}
                        <img
                            src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
                            alt=""
                            width="1"
                            height="1"
                            loading="lazy"
                        />
                    @endif
                </div>
            </a>
            <div class="actions-wrap">
                <a data-edit-image href="#" class="edit-action hidden">Edit</a>
                <a data-remove-image href="#" class="remove-action hidden">Remove</a>
            </div>
        </div>
    @endforeach
</div>