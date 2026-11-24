<x-modals.empty modal_id="browse-links" modal_inner_size="link-picker">
    <div class="p-4 h-full flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Browse links</h3>
            <a data-browse-files href="#" class="btn">Browse files</a>
        </div>

        <div class="relative w-full h-[30rem]">
            <div data-loader class="absolute inset-0 hidden flex-col items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span class="text-xl font-semibold">Loading links...</span>
            </div>
            <div data-link-wrapper class="h-full overflow-auto divide-y divide-gray-300 space-y-3"></div>
        </div>

        <div class="flex justify-end mt-4">
            <a
                data-modal-close href="#"
                class="btn btn--grey"
            >Close</a>
        </div>
    </div>
</x-modals.empty>