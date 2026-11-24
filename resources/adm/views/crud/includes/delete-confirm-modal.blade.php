<x-modals.empty modal_id="delete-confirm">
    <div class="p-4 h-full flex flex-col">
        <h3 class="text-lg font-semibold mb-1">Delete {{ $title }}?</h3>
        <p class="mb-4">Are you sure you want to delete the selected {{ strtolower($title) }}?</p>

        <div class="flex mt-4">
            <a
                data-confirm-btn href="#"
                class="btn btn--red mr-4"
            >Yes</a>
            <a
                data-modal-close href="#"
                class="btn btn--grey"
            >No</a>
        </div>
    </div>
</x-modals.empty>