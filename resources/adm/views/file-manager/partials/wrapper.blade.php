<div 
    data-file-manager="{{ isset($wrapper_type) ? $wrapper_type : 'modal' }}"
    data-auto-init="{{ isset($auto_init) && $auto_init ? 'true' : 'false' }}"
    data-max-file-uploads="{{ ini_get('max_file_uploads') < 5 ? ini_get('max_file_uploads') : 5 }}"
    class="file-manager w-full h-full min-h-[35rem] bg-white rounded-md shadow-md border border-gray-300 flex flex-wrap items-stretch{{ !isset($no_margin) || !$no_margin ? ' mt-4' : '' }}"
>
    <div
        data-sidebar
        class="fm-sidebar-wrap h-full"
    >
        <div data-folder-list class="folder-list">
        </div>
        <div class="bottom-actions">
            <a
                data-modal-open="add-folder" href="#"
                class="btn btn--grey inline-flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add folder
            </a>
        </div>
    </div>
    <div 
        data-files-wrapper
        class="fm-files-wrap"
    >
        <div data-dragdrop-wrap class="list-drag-drop">
            <div class="dd-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="dd-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m0-3l-3-3m0 0l-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75" />
                </svg>
                <strong>Drop file(s) to upload them.</strong>
            </div>
        </div>
        <div class="inner-wrap">
            <div class="top-actions">
                <h3 data-files-heading class="fm-heading">Site files</h3>
                <div class="actions-wrap">
                    <div class="search-field-wrap mr-3">
                        <input
                            data-search-input type="search" value=""
                            class="search-input" placeholder="Search files & folders"
                        />
                        <a 
                            data-search-btn href="#"
                            class="search-btn"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="search-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </a>
                    </div>
                    <a 
                        data-delete-folder-btn href="#"
                        class="hidden btn btn--red items-center whitespace-nowrap mr-3"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Delete folder
                    </a>
                    <a 
                        data-modal-open="add-folder" href="#"
                        class="btn btn--grey inline-flex items-center whitespace-nowrap mr-3"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add folder
                    </a>
                    <a
                        data-modal-open="upload-files" href="#"
                        class="btn inline-flex items-center whitespace-nowrap"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Upload file(s)
                    </a>
                </div>
            </div>
            <div 
                data-files-list
                class="files-list"
            ></div>
        </div>
    </div>

    <div data-fm-modals>
        @include('file-manager.partials.modals.add-folder')
        @include('file-manager.partials.modals.upload-files')
        @include('file-manager.partials.modals.view-file')
        @include('file-manager.partials.modals.delete-file')
        @include('file-manager.partials.modals.delete-folder')
        @include('file-manager.partials.modals.confirm-move')
    </div>
</div>