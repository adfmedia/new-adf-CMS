<x-modals.file-manager modal_id="delete-file">
    <x-slot:modal_content>
        <h3 class="text-base font-semibold leading-6 text-gray-900">Delete file?</h3>
        <p class="text-sm">Are you sure you want to delete this file?</p>
    </x-slot:modal_content>
    <x-slot:modal_actions>
        <a
            data-submit-btn href="#"
            class="btn btn--red ml-4"
        >Yes, delete the file</a>
        <a
            data-modal-close href="#"
            class="btn btn--grey"
        >Cancel</a>
    </x-slot:modal_actions>
</x-modals.file-manager>