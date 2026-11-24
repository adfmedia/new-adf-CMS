<x-modals.file-manager modal_id="add-folder">
    <x-slot:modal_content>
        <h3
            data-modal-title 
            class="text-base font-semibold leading-6 text-gray-900"
        >
            Add folder
        </h3>
        <form 
            data-modal-form
            action="#" method="post" enctype="multipart/form-data"
            class="mt-2"
        >
            <div class="field-wrap">
                <label for="folder-title" class="label">Title</label>
                <div class="input-wrap">
                    <input type="text" name="title" id="folder-title" class="input" required />
                </div>
            </div>
        </form>
    </x-slot:modal_content>
    <x-slot:modal_actions>
        <a
            data-submit-btn href="#"
            class="btn btn--save ml-4"
        >Add folder</a>
        <a
            data-modal-close href="#"
            class="btn btn--grey"
        >Cancel</a>
    </x-slot:modal_actions>
</x-modals.file-manager>