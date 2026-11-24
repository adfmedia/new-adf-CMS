<x-modals.empty modal_id="page-builder-{{ $pb_id }}" modal_inner_size="page-builder">
    <div class="p-4 h-full flex flex-col overflow-y-auto">
        <h3 class="text-lg font-semibold mb-1">Page builder</h3>
        <p class="mb-4">Select a block to add it to the page</p>

        <div class="flex-1 relative">
            <div 
                data-loader
                class="absolute inset-0 z-10 bg-white flex items-center justify-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            <div 
                data-block-wrapper
                class="flex flex-wrap items-stretch -mx-4"
            >
            </div>
        </div>
    </div>
</x-modals.empty>

<x-modals.empty modal_id="page-builder-remove-{{ $pb_id }}">
    <h3 class="text-base font-semibold leading-6 text-gray-900">Remove block?</h3>
    <p class="text-sm">Are you sure you want to remove this block?</p>
    <div class="flex mt-4">
        <a
            data-confirm-btn href="#"
            class="btn btn--red mr-4"
        >Yes, remove the block</a>
        <a
            data-modal-close href="#"
            class="btn btn--grey"
        >Cancel</a>
    </div>
</x-modals.empty>