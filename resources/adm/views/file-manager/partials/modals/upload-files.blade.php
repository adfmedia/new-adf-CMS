<x-modals.file-manager modal_id="upload-files">
    <x-slot:modal_content>
        <h3
            data-modal-title 
            class="text-base font-semibold leading-6 text-gray-900"
        >
            Upload files
        </h3>
        <form 
            data-modal-form
            action="#" method="post" enctype="multipart/form-data"
            class="upload-files-wrap mt-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="uf-icon-loading">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <div class="uf-input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="uf-icon-upload">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m0-3l-3-3m0 0l-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75" />
                </svg>
                <input
                    data-uf-input
                    type="file" name="files[]" multiple id="file-upload"
                    class="uf-input"
                />
                <label data-uf-label for="file-upload" class="uf-label">
                    <strong>Choose file(s) to upload</strong>
                    <span class="uf-drag-drop">, or drag them here</span>.
                    <span 
                        data-selected-label data-multiple-caption="{count} files selected"
                        class="uf-selected-label"
                    ></span>
                </label>
                <button class="uf-btn" type="submit">Upload</button>
            </div>
            <div 
                data-uploading-message-wrap
                class="uf-uploading"
            >
                <div><span data-uploading-message>Uploading</span>{{ config('services.tinify.enabled') && config('services.tinify.api_key') ? ' and optimizing' : '' }}&hellip;</div>
                <div class="progress-wrap">
                    <div data-uploading-progress class="progress-bar"></div>
                    <div data-uploading-progress-message class="progress-message"></div>
                </div>
            </div>
            <div class="uf-success">Files uploaded</div>
            <div class="uf-error">Sorry, something went wrong.<span data-uf-error></span></div>
        </form>
    </x-slot:modal_content>
    <x-slot:modal_actions>
        <a
            data-modal-close href="#"
            class="btn btn--grey"
        >Cancel</a>
    </x-slot:modal_actions>
</x-modals.file-manager>