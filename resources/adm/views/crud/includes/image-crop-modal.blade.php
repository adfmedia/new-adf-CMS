<x-modals.empty modal_id="image-crop-modal" modal_inner_size="image-picker" hide_close="true">
    <div class="p-4 h-full flex flex-col overflow-y-auto">
        <h3 class="text-lg font-semibold mb-1">Edit image</h3>
        <p class="mb-4">Select a different area of the image, or crop an area out.</p>

        <div class="flex-1 relative p-4">
            <div 
                data-loader
                class="hidden absolute inset-0 z-10 bg-white flex items-center justify-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            <div data-image-crop-wrapper class="image-crop-wrap">
                <img
                    data-preview-image
                    src="" alt="Image preview"
                    width="" height=""
                    {{-- loading="lazy" --}}
                    class="preview-image"
                >
                {{--
                <div class="crop-area-wrap">
                    <div class="crop-area-bg"></div>
                    <div data-crop-area class="crop-area">
                        <div data-handle="nw" class="handle nw"></div>
                        <div data-handle="ne" class="handle ne"></div>
                        <div data-handle="sw" class="handle sw"></div>
                        <div data-handle="se" class="handle se"></div>
                        <div data-handle="move" class="handle move"></div>
                    </div>
                </div>
                --}}
            </div>
        </div>
    </div>
    <div class="flex justify-center p-4 bg-gray-100">
        <a
            data-submit-btn href="#"
            class="btn btn--save mr-4"
        >Save &amp; close</a>
        <a
            data-close-btn href="#"
            class="btn btn--grey"
        >Close</a>
    </div>
</x-modals.empty>