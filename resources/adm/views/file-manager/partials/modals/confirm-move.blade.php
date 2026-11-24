<x-modals.file-manager modal_id="confirm-move">
    <x-slot:modal_content>
        <h3 class="text-base font-semibold leading-6 text-gray-900">Move <span data-resource-type></span>?</h3>
        <p class="text-sm">Are you sure you want to move <span data-resource-title class="font-semibold"></span> to <span data-dest-title class="font-semibold"></span>?</p>
    </x-slot:modal_content>
    <x-slot:modal_actions>
        <a
            data-submit-btn href="#"
            class="btn btn--save ml-4"
        >Yes</a>
        <a
            data-modal-close href="#"
            class="btn btn--grey"
        >No, cancel</a>
    </x-slot:modal_actions>
</x-modals.file-manager>