<x-modals.file-manager modal_id="view-file" modal_inner_size="view-file"  modal_action_class="pl-4 pt-6 mt-0 flex-row border-t border-gray-300">
    <x-slot:modal_content>
        <div data-modal-inner class="view-file-modal">
            <div data-loader class="loader">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="vf-icon-loading">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            <div data-detail-list class="detail-list">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">File details</h3>
                <div data-title class="mb-1">
                    <div class="font-semibold">File name:</div>
                    <span data-value></span>
                </div>
                <div data-dimensions class="hidden mb-1">
                    <span class="font-semibold">Dimensions:</span>
                    <span data-value></span>
                </div>
                <div data-file-size class="mb-1">
                    <span class="font-semibold">File size:</span>
                    <span data-value class="uppercase"></span>
                </div>
                <div data-extension class="mb-1">
                    <span class="font-semibold">File type:</span>
                    <span data-value class="uppercase"></span>
                </div>
                <div data-uploaded class="mb-4">
                    <span class="font-semibold">Uploaded:</span>
                    <span data-value></span>
                </div>
                <div class="flex items-center justify-between">
                    <a
                        data-view-link
                        href="#" target="_blank"
                        class="btn"
                    >Open file</a>
                    <div class="inline-flex items-center justify-end">
                        <label for="vf-replace-inpt" class="cursor-pointer text-primary-medium hover:underline mr-2">Replace</label>
                        <a data-delete-link href="#" class="text-red-600 hover:underline">Delete</a>
                    </div>
                </div>

                <hr class="border-t border-gray-300 my-6">

                <div data-url class="mb-2">
                    <div class="font-semibold">File URL:</div>
                    <div data-value></div>
                </div>

                <form 
                    data-modal-form
                    action="#" method="post" enctype="multipart/form-data"
                >
                    <input data-input="file_id" name="file" type="hidden" value="" />
                    <div class="field-wrap">
                        <label for="file-title" class="label">File name</label>
                        <div class="help-text">Update the name of the file</div>
                        <div class="input-wrap">
                            <input data-input="title" type="text" name="title" id="file-title" class="input" required />
                        </div>
                    </div>
                    <div data-alt-field class="hidden field-wrap">
                        <label for="alt-text" class="label">Alt text</label>
                        <div class="help-text">Update the alt text of the file</div>
                        <div class="input-wrap">
                            <input data-input="alt_text" type="text" name="alt_text" id="alt-text" class="input" />
                        </div>
                    </div>
                </form>

                <form
                    data-replace-form
                    action="#" method="post" enctype="multipart/form-data"
                    class="hidden"
                >
                    <input data-input="file_id" name="file_id" type="hidden" value="" />
                    <input data-file-input id="vf-replace-inpt" name="file" type="file" required />
                </form>
            </div>
            <div data-file-pane class="file-pane">
                <div data-flash="error" class="flash error"></div>
                <div data-flash="success" class="flash success"></div>
                
                <div class="hidden image-panel">
                    <img
                        data-image-preview
                        src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
                        data-src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
                        width="1"
                        height="1"
                        loading="lazy"
                    />
                    <div
                        data-controls-wrap
                        class="absolute inset-0 flex items-end justify-center p-2 hidden"
                    >
                        <div
                            data-rotate-controls
                            class="bg-white rounded-md p-1 flex items-center shadow-md"
                        >
                            <a
                                data-rotate-link="anticlockwise" href="#" title="Rotate 90&deg; anti-clockwise"
                                class="transition-colors hover:text-primary-medium"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"
                                    class="w-4 h-4"
                                >
                                    <path d="M440-80q-50-5-96-24.5T256-156l56-58q29 21 61.5 34t66.5 18v82Zm80 0v-82q104-15 172-93.5T760-438q0-117-81.5-198.5T480-718h-8l64 64-56 56-160-160 160-160 56 58-62 62h6q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-438q0 137-91 238.5T520-80ZM198-214q-32-42-51.5-88T122-398h82q5 34 18 66.5t34 61.5l-58 56Zm-76-264q6-51 25-98t51-86l58 56q-21 29-34 61.5T204-478h-82Z"/>
                                </svg>
                            </a>
                            <span class="h-3 border-l border-gray-500 mx-2"></span>
                            <a
                                data-rotate-link="clockwise" href="#" title="Rotate 90&deg; clockwise"
                                class="transition-colors hover:text-primary-medium"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"
                                    class="w-4 h-4 -scale-x-100"
                                >
                                    <path d="M440-80q-50-5-96-24.5T256-156l56-58q29 21 61.5 34t66.5 18v82Zm80 0v-82q104-15 172-93.5T760-438q0-117-81.5-198.5T480-718h-8l64 64-56 56-160-160 160-160 56 58-62 62h6q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-438q0 137-91 238.5T520-80ZM198-214q-32-42-51.5-88T122-398h82q5 34 18 66.5t34 61.5l-58 56Zm-76-264q6-51 25-98t51-86l58 56q-21 29-34 61.5T204-478h-82Z"/>
                                </svg>
                            </a>
                            <span class="h-3 border-l border-gray-500 mx-2"></span>
                            <a
                                data-flip-link="vertical" href="#" title="Flip vertically"
                                class="transition-colors hover:text-primary-medium"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"
                                    class="w-4 h-4 rotate-90"
                                >
                                    <path d="M360-120H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h160v80H200v560h160v80Zm80 80v-880h80v880h-80Zm160-80v-80h80v80h-80Zm0-640v-80h80v80h-80Zm160 640v-80h80q0 33-23.5 56.5T760-120Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80q33 0 56.5 23.5T840-760h-80Z"/>
                                </svg>
                            </a>
                            <span class="h-3 border-l border-gray-500 mx-2"></span>
                            <a
                                data-flip-link="horizontal" href="#" title="Flip horizontally"
                                class="transition-colors hover:text-primary-medium"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"
                                    class="w-4 h-4"
                                >
                                    <path d="M360-120H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h160v80H200v560h160v80Zm80 80v-880h80v880h-80Zm160-80v-80h80v80h-80Zm0-640v-80h80v80h-80Zm160 640v-80h80q0 33-23.5 56.5T760-120Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80q33 0 56.5 23.5T840-760h-80Z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="hidden file-panel">
                    <iframe
                        data-file-preview
                        src="about:blank"
                        data-src="about:blank"
                        loading="lazy"
                        class="preview-frame"
                    ></iframe>
                    <div class="no-preview">
                        <div class="w-80 h-64 bg-gray-200 rounded-md flex flex-col items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 mb-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <div class="">File preview unavailable</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:modal_content>
    <x-slot:modal_actions>
        <a
            data-submit-btn href="#"
            class="btn btn--save mr-4"
        >Save file details</a>
        <a
            data-modal-close href="#"
            class="btn btn--grey"
        >Cancel</a>
    </x-slot:modal_actions>
</x-modals.file-manager>