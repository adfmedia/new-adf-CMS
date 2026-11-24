//import 'cropperjs/dist/cropper.min.css'

class FileManager {
    #wrapperEl;
    #sidebar;
    #folderList;
    #filesWrap;
    #filesHeading;
    #filesList;

    #searchInput;
    #searchBtn;

    #uploadFilesModal;
    #uploadFilesForm;
    #uploadDroppedFiles = false;
    #max_file_uploads = 5;
    #uploaded_file_ids = [];

    #fileListDroppable;
    #fileListDroppableTimer;

    #viewFileModal;
    #deleteFileModal;
    #deleteFolderBtn;
    #deleteFolderModal;
    #confirmMoveModal;

    #fmType;
    #scopedToImages = false;
    #targetInput;
    #targetType;

    #selectedFile;

    folder = {
        id: 0,
        title: 'Site files'
    };
    expanded_folders = [];

    constructor(wrapperEl) {
        this.#wrapperEl = wrapperEl;
        this.#fmType = wrapperEl.dataset.fileManager;
        this.#sidebar = this.#wrapperEl.querySelector('[data-sidebar]');
        this.#folderList = this.#sidebar.querySelector('[data-folder-list]');
        this.#filesWrap = this.#wrapperEl.querySelector('[data-files-wrapper]');
        this.#filesHeading = this.#filesWrap.querySelector('[data-files-heading]');
        this.#filesList = this.#filesWrap.querySelector('[data-files-list]');
        this.#viewFileModal = wrapperEl.querySelector('[data-fm-modal="view-file"]');
        this.#deleteFileModal = wrapperEl.querySelector('[data-fm-modal="delete-file"]');
        this.#deleteFolderBtn = wrapperEl.querySelector('[data-delete-folder-btn]');
        this.#deleteFolderModal = wrapperEl.querySelector('[data-fm-modal="delete-folder"]');
        this.#searchInput = wrapperEl.querySelector('[data-search-input]');
        this.#searchBtn = wrapperEl.querySelector('[data-search-btn]');
        this.#confirmMoveModal = wrapperEl.querySelector('[data-fm-modal="confirm-move"]');

        this.#max_file_uploads = parseInt(wrapperEl.dataset.maxFileUploads);

        this.#listingTileDragDropFunctionBinds();

        if (wrapperEl.hasAttribute('data-auto-init') && wrapperEl.dataset.autoInit === "true") {
            this.#init();
        }
    }

    #init() {
        this.#loadFolders();
        this.#loadFiles();

        this.#bindFolderEvents();
        this.#bindFileEvents();
        this.#bindSearch();

        this.#bindModalToggles();
        this.#bindModalForms();
    }

    initWithTarget(input, type, scope, prev_file, prev_folder) {
        // Reset
        this.#scopedToImages = false;
        this.#selectedFile = null;

        this.#targetInput = input;
        this.#targetType = type;

        if (scope == 'image') {
            this.#scopedToImages = true;
        }
        if (prev_file) {
            this.#selectedFile = prev_file;
        }
        if (prev_folder) {
            const prev_folder_details = JSON.parse(prev_folder);
            this.folder.id = prev_folder_details.id;
            this.folder.title = prev_folder_details.title;
        }
        this.#init();
    }

    #loadFiles(search) {
        let payload = { folder: this.folder.id, image_scope: (this.#scopedToImages ? 1 : 0) };
        if (typeof search != 'undefined') {
            payload.search = search;
        }

        this.#fetchRequest('load_files', payload)
            .then(json => {
                let files_list = ``;
                if (json.files.length || json.folders.length) {
                    if (json.folders.length) {
                        for (const folder of json.folders) {
                            files_list += `
                                <div class="listing-tile folder" draggable="true">
                                    <a
                                        data-folder-link="${folder.id}" data-title="${folder.title}"
                                        href="#"
                                        class="tile-link"
                                        draggable="false"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tile-icon">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                        </svg>   
                                        <span class="tile-title">${folder.title}</span>
                                    </a>
                                </div>
                            `;
                        }
                        if (json.files.length) {
                            files_list += '<div class="col-span-full"></div>';
                        }
                    }
                    if (json.files.length) {
                        for (const file of json.files) {
                            let file_image = `
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tile-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            `;
                            if (file.is_image) {
                                file_image = `
                                    <img
                                        src="${file.image.src}"
                                        alt="${file.image.alt}"
                                        width="${file.image.width}"
                                        height="${file.image.height}"
                                        loading="lazy"
                                        class="tile-image"
                                        draggable="false"
                                    />
                                `;
                            } else {
                                switch (file.icon_type) {
                                    case 'video':
                                        file_image = `
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="tile-icon">
                                                <path d="m140-800 74 152h130l-74-152h89l74 152h130l-74-152h89l74 152h130l-74-152h112q24 0 42 18t18 42v520q0 24-18 42t-42 18H140q-24 0-42-18t-18-42v-520q0-24 18-42t42-18Zm0 212v368h680v-368H140Zm0 0v368-368Z"/>
                                            </svg>
                                        `;
                                        break;
                                    case 'spreadsheet':
                                        file_image = `
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tile-icon">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                                            </svg>
                                        `;
                                        break;
                                }
                            }

                            const tile_classes = [
                                'listing-tile',
                                'file'
                            ];
                            if (this.#uploaded_file_ids.includes(parseInt(file.id))) {
                                tile_classes.push('is-new');
                            }

                            files_list += `
                                <div class="${tile_classes.join(' ')}" draggable="true">
                                    <a
                                        data-file-link="${file.id}" data-title="${file.title}"
                                        href="#"
                                        class="tile-link"
                                        draggable="false"
                                    >
                                        <div class="tile-image-wrap">
                                            ${file_image}
                                        </div>
                                        <span class="tile-title">${file.title}</span>
                                    </a>
                                </div>
                            `;
                        }
                    }
                } else {
                    files_list = `
                        <div class="w-full pl-2">
                            <p class="mb-4">No files found in ${(this.folder.title).toLowerCase()}.</p>
                        </div>
                    `;
                }

                this.#filesList.innerHTML = files_list;
                this.#uploaded_file_ids = [];
                this.#bindListingTileDragDrop();

                const newFile = this.#filesList.querySelector('.listing-tile.is-new');
                if (newFile) {
                    newFile.scrollIntoView();
                }
            });
    }

    #loadFolders() {
        this.#fetchRequest('load_folders', {parent: this.folder.id, expanded: this.expanded_folders})
            .then(json => {
                let folder_list = ``;
                for (const folder of json.folders) {
                    if (folder.expanded && !this.#isFolderExpanded(folder.id)) {
                        this.#folderExpanded(folder.id);
                    }

                    folder_list += this.#generateFolderList(folder, (folder.id == 0));
                }

                this.#folderList.innerHTML = folder_list;

                this.#bindSidebarFolderDragDrop();
            });
    }
    #generateFolderList(folder, no_padding) {
        let child_list = ``;
        let child_expand = ``;
        if (folder.children.length) {
            child_list += `<div data-child-list class="${folder.expanded ? '' : ' hidden'}">`;
            for (const child of folder.children) {
                if (child.expanded && !this.#isFolderExpanded(child.id)) {
                    this.#folderExpanded(child.id);
                }

                child_list += this.#generateFolderList(child);
            }
            child_list += `</div>`;

            child_expand = `<div data-expand="${folder.id}" class="expander${folder.expanded ? ' open' : ''}">&#9660;</div>`;
        }

        return `
            <div data-folder-group class="relative mb-1${no_padding ? '' : ' pl-4'}">
                ${child_expand}
                <a 
                    data-folder-link="${folder.id}" data-title="${folder.title}"
                    href="#"
                    class="folder-link${folder.selected ? ' font-bold' : ''}"
                >${folder.title}</a>
                ${child_list}
            </div>
        `;
    }

    #selectFolder(id, title) {
        this.folder.id = id;
        this.folder.title = title;

        if (id > 0) {
            this.#deleteFolderBtn.classList.remove('hidden');
            this.#deleteFolderBtn.classList.add('inline-flex');
        } else {
            this.#deleteFolderBtn.classList.add('hidden');
            this.#deleteFolderBtn.classList.remove('inline-flex');
        }

        this.#filesHeading.innerText = title;
        this.#loadFolders();
        this.#loadFiles();
    }

    #expandedFolderIdx(id) {
        return this.expanded_folders.indexOf(parseInt(id));
    }
    #isFolderExpanded(id) {
        return this.#expandedFolderIdx(id) !== -1;
    }
    #folderExpanded(id) {
        this.expanded_folders.push(parseInt(id));
    }
    #folderCollapsed(id) {
        this.expanded_folders.splice(this.#expandedFolderIdx(id), 1);
    }

    #bindFolderEvents() {
        this.#folderList.addEventListener('click', (e) => {
            const evtTarget = e.target;
            if (evtTarget.hasAttribute('data-folder-link')) {
                e.preventDefault();
                this.#selectFolder(evtTarget.dataset.folderLink, evtTarget.dataset.title);
            }
            else if (evtTarget.hasAttribute('data-expand')) {
                e.preventDefault();
                
                const hiddenChildren = evtTarget.closest('[data-folder-group]').querySelector('[data-child-list]');
                if (evtTarget.classList.contains('open')) {
                    if (this.#isFolderExpanded(evtTarget.dataset.expand)) {
                        this.#folderCollapsed(evtTarget.dataset.expand);
                    }
                    evtTarget.classList.remove('open');
                    hiddenChildren.classList.add('hidden');
                } else {
                    evtTarget.classList.add('open');
                    if (!this.#isFolderExpanded(evtTarget.dataset.expand)) {
                        this.#folderExpanded(evtTarget.dataset.expand);
                    }
                    hiddenChildren.classList.remove('hidden');
                }
            }
        });

        this.#filesList.addEventListener('click', (e) => {
            let evtTarget = e.target;
            if (evtTarget.hasAttribute('data-folder-link') || evtTarget.closest('[data-folder-link]')) {
                e.preventDefault();
                if (!evtTarget.hasAttribute('data-folder-link')) {
                    evtTarget = evtTarget.closest('[data-folder-link]');     
                }
                this.#selectFolder(evtTarget.dataset.folderLink, evtTarget.dataset.title);
            }
        });

        for (const addFolderBtn of this.#wrapperEl.querySelectorAll('[data-add-folder]')) {
            addFolderBtn.onclick = (e) => {
                e.preventDefault();
            }
        }
    }
    #bindFileEvents() {
        // View file details
        this.#filesList.addEventListener('click', (e) => {
            let evtTarget = e.target;
            if (evtTarget.hasAttribute('data-file-link') || evtTarget.closest('[data-file-link]')) {
                if (!evtTarget.hasAttribute('data-file-link')) {
                    evtTarget = evtTarget.closest('[data-file-link]');
                }

                if (this.#fmType == 'full') {
                    e.preventDefault();
                    this.#loadViewFileModal(evtTarget.dataset.fileLink);
                }
                else if (this.#fmType == 'modal') {
                    e.preventDefault();
                    if (this.#targetInput) {
                        let evt_details = {
                            file: evtTarget.dataset.fileLink
                        };
                        const evt_name = this.#targetType == 'image' ? 'image-selected' : 'file-selected';
                        this.#targetInput.dispatchEvent(new CustomEvent(evt_name, {detail: evt_details}));
                    } else {
                        console.error('Target input not set!');
                    }
                }
            }
        });
    }
    
    #bindSearch() {
        this.#searchInput.onkeyup = (e) => {
            if (e.which == 13) {
                this.#performSearch();
            }
        }
        this.#searchBtn.onclick = (e) => {
            e.preventDefault();
            this.#performSearch();
        }
    }
    #performSearch() {
        this.folder.id = 0;
        this.folder.title = 'Search';
        this.#filesHeading.innerText = 'Search results';
        
        this.#loadFiles(this.#searchInput.value);
    }

    #bindModalToggles() {
        const modalOpeners = this.#wrapperEl.querySelectorAll('[data-modal-open]');
        for (const modalOpener of modalOpeners) {
            modalOpener.onclick = (e) => {
                e.preventDefault();

                const tgtModal = this.#wrapperEl.querySelector('[data-fm-modals] [data-fm-modal="' + modalOpener.dataset.modalOpen + '"]');
                if (tgtModal) {
                    tgtModal.classList.add('open');
                }
            }
        }

        const modalClosers = this.#wrapperEl.querySelectorAll('[data-modal-close]');
        for (const modalClose of modalClosers) {
            modalClose.onclick = (e) => {
                e.preventDefault();

                const modal = modalClose.closest('[data-fm-modal]');
                if (modal) {
                    modal.classList.remove('open');
                }
            }
        }
    }

    #bindModalForms() {
        const addFolderModal = this.#wrapperEl.querySelector('[data-fm-modal="add-folder"]');
        if (addFolderModal) {
            const form = addFolderModal.querySelector('[data-modal-form]');
            const btn = addFolderModal.querySelector('[data-submit-btn]');

            form.onsubmit = (e) => {
                e.preventDefault();
                btn.dispatchEvent(new Event('click'));
                return false;
            };

            btn.onclick = (e) => {
                e.preventDefault();

                const valid = form.checkValidity();
                if (!valid) {
                    form.reportValidity();
                    return;
                }

                const data = new FormData(form);
                data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
                data.append('parent_id', this.folder.id);

                fetch(
                    '/adm/file-manager/ajax/add_folder',
                    {
                        method: 'post',
                        body: data,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                )
                .then(resp => resp.json())
                .then(json => {
                    if (json.success) {
                        this.#selectFolder(json.id, json.title);
                        addFolderModal.classList.remove('open');
                    }
                });
            };
        }

        // Delete folder
        this.#deleteFolderBtn.onclick = (e) => {
            e.preventDefault();

            if (this.folder.id == 0) { return false; }

            this.#deleteFolderModal.querySelector('[data-submit-btn]').onclick = (e) => {
                this.#fetchRequest('delete_folder', { folder: this.folder.id })
                    .then(json => {
                        let folder_id = 0;
                        let folder_title = 'Site files';
                        if (json.folder) {
                            folder_id = json.folder.id;
                            folder_title = json.folder.title;
                        }

                        this.#selectFolder(folder_id, folder_title);
                        this.#deleteFolderModal.classList.remove('open');
                    });
            };
            this.#deleteFolderModal.classList.add('open');
        };

        // Upload files
        this.#uploadFilesModal = this.#wrapperEl.querySelector('[data-fm-modal="upload-files"]');
        if (this.#uploadFilesModal) {
            const form = this.#uploadFilesModal.querySelector('[data-modal-form]');
            // Detect drag/drop
            const checkDiv = document.createElement('div');
            const dragDropSupported = (('draggable' in checkDiv) || ('ondragstart' in checkDiv && 'ondrop' in checkDiv));

            if (form) {
                this.#uploadFilesForm = form;
                if (dragDropSupported) {
                    form.classList.add('dd-enabled');

                    this.#fileListDroppable = this.#filesWrap.querySelector('[data-dragdrop-wrap]');

                    const prevent_evts = ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'];
                    const enter_evts = ['dragover', 'dragenter'];
                    const leave_evts = ['dragleave', 'dragend', 'drop'];
                    for (const prev_evt of prevent_evts) {
                        form.addEventListener(prev_evt, this.#dragdropPrevent);
                        this.#filesWrap.addEventListener(prev_evt, this.#dragdropPrevent);
                    }
                    for (const enter_evt of enter_evts) {
                        form.addEventListener(enter_evt, this.#dragdropOverEnter.bind(this));
                        this.#filesWrap.addEventListener(enter_evt, this.#fileListDragDropOverEvent.bind(this));
                    }
                    for (const leave_evt of leave_evts) {
                        form.addEventListener(leave_evt, this.#dragdropLeaveEndDrop.bind(this));
                        this.#filesWrap.addEventListener(leave_evt, this.#fileListDragDropLeaveEndEvent.bind(this));
                    }
                    form.addEventListener('drop', this.#dragdropDrop.bind(this));
                    this.#fileListDroppable.addEventListener('drop', this.#fileListDragDropDrop.bind(this));
                }

                const uploadFileInput = form.querySelector('[data-uf-input]');
                uploadFileInput.addEventListener('change', (e) => {
                    this.#uploadFilesForm.dispatchEvent(new Event('submit', {cancelable: true}));
                });

                form.addEventListener('submit', (e) => {
                    e.preventDefault();

                    if (form.classList.contains('is-uploading')) { return false; }

                    form.classList.add('is-uploading');
                    form.classList.remove('is-error');                    

                    let batch_upload = false;
                    if (dragDropSupported && this.#uploadDroppedFiles != false) {
                        if (this.#uploadDroppedFiles.length > this.#max_file_uploads) {
                            batch_upload = true;
                        }
                    }
                    if (uploadFileInput.files && uploadFileInput.files.length > this.#max_file_uploads) {
                        batch_upload = true;
                    }

                    if (batch_upload) {
                        this.#batchUploadFiles()
                            .then((responses) => {
                                let num_uploaded = 0;
                                let resp_json = {};
                                let resp_reason = false;

                                for (const response of responses) {
                                    if (response.success) {
                                        num_uploaded += parseInt(response.uploaded);
                                        resp_json = response;
                                    }
                                    else if (response.error) {
                                        resp_reason = response.error;
                                    }
                                }

                                if (resp_reason !== false) {
                                    this.#uploadFilesError(null, resp_reason, num_uploaded);
                                } else {
                                    this.#uploadFilesSuccess(resp_json);
                                }
                            })
                            .finally(() => {
                                this.#uploadDroppedFiles = false;
                            });
                    } else {
                        let postBody = new FormData(form);
                        postBody.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
                        postBody.append('folder_id', this.folder.id);
                        if (dragDropSupported && this.#uploadDroppedFiles) {
                            for (const file of this.#uploadDroppedFiles) {
                                postBody.append('files[]', file);
                            }
                        }

                        this.#uploadFiles(postBody)
                            .then(json => {
                                this.#uploadFilesSuccess(json);

                                for (const id of json.uploaded_ids) {
                                    this.#uploaded_file_ids.push(parseInt(id));
                                }
                            })
                            .catch((reason) => {
                                this.#uploadFilesError(reason, '', 0);
                            })
                            .finally(() => {
                                this.#uploadDroppedFiles = false;
                            });
                    }
                });
            }
        }

        // View file modal - update details, replace file
        if (this.#viewFileModal) {
            const updFileForm = this.#viewFileModal.querySelector('[data-modal-form]');
            const btn = this.#viewFileModal.querySelector('[data-submit-btn]');

            btn.onclick = (e) => {
                e.preventDefault();

                const valid = updFileForm.checkValidity();
                if (!valid) {
                    updFileForm.reportValidity();
                    return;
                }

                const data = new FormData(updFileForm);
                data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));

                fetch(
                    '/adm/file-manager/ajax/save_file_details',
                    {
                        method: 'post',
                        body: data,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                )
                .then(resp => resp.json())
                .then(json => {
                    if (json.success) {
                        this.#viewFileFlash('File details updated.', 'success');
                        this.#loadViewFileModal(json.file_id, true);
                        this.#selectFolder(this.folder.id, this.folder.title);
                    } else {
                        this.#viewFileFlash(json.error, 'error');
                    }
                });
            }

            const replaceForm = this.#viewFileModal.querySelector('[data-replace-form]');
            if (replaceForm) {
                const fileInpt = replaceForm.querySelector('[data-file-input]');
                if (fileInpt) {
                    replaceForm.onsubmit = (e) => {
                        e.preventDefault();

                        const data = new FormData(replaceForm);
                        data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));

                        fetch(
                            '/adm/file-manager/ajax/replace_file',
                            {
                                method: 'post',
                                body: data,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }
                        )
                        .then(resp => resp.json())
                        .then(json => {
                            if (json.success) {
                                this.#viewFileFlash('File replaced successfully', 'success');
                                this.#loadViewFileModal(json.file_id, true);
                                this.#selectFolder(this.folder.id, this.folder.title);
                            } else {
                                this.#viewFileFlash(json.error, 'error');
                            }
                            fileInpt.value = null;
                        });
                    };
                    fileInpt.onchange = () => {
                        replaceForm.requestSubmit();
                    };
                }
            }

            const deleteLink = this.#viewFileModal.querySelector('[data-delete-link]');
            if (this.#deleteFileModal) {
                deleteLink.onclick = (e) => {
                    e.preventDefault();
                    this.#deleteFileModal.classList.add('open');

                    this.#deleteFileModal.querySelector('[data-submit-btn]').onclick = (e) => {
                        e.preventDefault();

                        const fileInpt = this.#viewFileModal.querySelector('[data-input="file_id"]');
                        this.#fetchRequest('delete_file', { file: fileInpt.value })
                            .then(json => {
                                if (json.success) {
                                    this.#viewFileFlash('File deleted', 'success');
                                    setTimeout(() => {
                                        this.#viewFileModal.classList.remove('open');
                                    });
                                    this.#selectFolder(this.folder.id, this.folder.title);
                                } else {
                                    this.#viewFileFlash(json.error, 'error');
                                }
                                this.#deleteFileModal.classList.remove('open');
                            });
                    };
                };
            } else {
                deleteLink.classList.add('hidden');
            }

            const rotateLinks = this.#viewFileModal.querySelectorAll('[data-rotate-link]');
            if (rotateLinks.length) {
                for (const rotateLink of rotateLinks) {
                    rotateLink.onclick = (e) => {
                        e.preventDefault();

                        const fileInpt = this.#viewFileModal.querySelector('[data-input="file_id"]');
                        this.#fetchRequest('manipulate_file', { 
                                file: fileInpt.value, 
                                op: 'rotate',
                                dir: rotateLink.dataset.rotateLink 
                            })
                            .then(json => {
                                if (json.success) {
                                    this.#viewFileFlash('File rotated', 'success');
                                    this.#loadViewFileModal(json.file_id, true);
                                    this.#loadFiles();
                                } else {
                                    this.#viewFileFlash(json.error, 'error');
                                }
                            });
                    };
                }
            }

            const flipLinks = this.#viewFileModal.querySelectorAll('[data-flip-link]');
            if (flipLinks.length) {
                for (const flipLink of flipLinks) {
                    flipLink.onclick = (e) => {
                        e.preventDefault();

                        const fileInpt = this.#viewFileModal.querySelector('[data-input="file_id"]');
                        this.#fetchRequest('manipulate_file', { 
                                file: fileInpt.value, 
                                op: 'flip',
                                dir: flipLink.dataset.flipLink 
                            })
                            .then(json => {
                                if (json.success) {
                                    this.#viewFileFlash('File flipped', 'success');
                                    this.#loadViewFileModal(json.file_id, true);
                                    this.#loadFiles();
                                } else {
                                    this.#viewFileFlash(json.error, 'error');
                                }
                            });
                    };
                }
            }
        }
    }

    async #uploadFiles(postBody) {
        return fetch(
            '/adm/file-manager/ajax/upload_files',
            {
                method: 'post',
                body: postBody,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(resp => resp.json());
    }
    async #batchUploadFiles() {
        const uplMsgWrap = this.#uploadFilesForm.querySelector('[data-uploading-message-wrap]');
        const uplMsg = uplMsgWrap.querySelector('[data-uploading-message]');
        const progress = uplMsgWrap.querySelector('[data-uploading-progress]');
        const progressMsg = uplMsgWrap.querySelector('[data-uploading-progress-message]');
        const uploadFileInput = this.#uploadFilesForm.querySelector('[data-uf-input]');

        let filesToUpload = uploadFileInput.files;
        if (this.#uploadDroppedFiles && this.#uploadDroppedFiles.length) {
            filesToUpload = this.#uploadDroppedFiles;
        }

        let uploaded_files = 0;
        let processed_files = 0;
        const num_files = filesToUpload.length;
        uplMsg.innerText = `Uploading ${num_files} file${num_files > 1 ? 's' : ''}`;

        const responses = [];
        while (processed_files < num_files) {
            const upl_from = processed_files;
            let upl_to = upl_from + this.#max_file_uploads;
            if (upl_to > num_files) {
                upl_to = num_files;
            }

            let postBody = new FormData();
            postBody.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
            postBody.append('folder_id', this.folder.id);
            for (let idx = upl_from; idx < upl_to; idx++) {
                postBody.append('files[]', filesToUpload[idx]);
                processed_files++;
            }

            const response = this.#uploadFiles(postBody);
            responses.push(response);
            response.then(json => {
                if (json.success && json.uploaded) {
                    uploaded_files += parseInt(json.uploaded);
                    progressMsg.innerText = uploaded_files + '/' + num_files;
                    progress.style.width = parseInt((uploaded_files / num_files) * 100) + '%';

                    for (const id of json.uploaded_ids) {
                        this.#uploaded_file_ids.push(parseInt(id));
                    }
                }
            });
        }

        return Promise.all(responses);
    }
    #uploadFilesSuccess(json) {
        if (json.success) {
            this.#uploadFilesForm.classList.add('is-success');

            setTimeout(() => {
                this.#uploadFilesModal.classList.remove('open');
                this.#uploadFilesForm.classList.remove('is-success');
                this.#dragDropHideFiles();
                this.#selectFolder(json.folder_id, json.folder_title);
            }, 1500);
        }
        else {
            this.#dragDropHideFiles();
            this.#uploadFilesForm.classList.add('is-error');
            this.#uploadFilesForm.querySelector('[data-uf-error]').innerText = json.error;
        }

        this.#uploadFilesForm.classList.remove('is-uploading');
        this.#filesWrap.classList.remove('is-uploading');
    }
    #uploadFilesError(reason, message, num_uploaded) {
        if (typeof num_uploaded == 'undefined') {
            num_uploaded = 0;
        }

        let error_message = message;
        if (num_uploaded) {
            error_message += ', but ' + num_uploaded + ' of your file(s) were uploaded successfully.';
        }

        this.#dragDropHideFiles();
        this.#uploadFilesForm.classList.add('is-error');
        this.#uploadFilesForm.querySelector('[data-uf-error]').innerText = error_message;
        this.#uploadFilesForm.classList.remove('is-uploading');
        this.#fileListDroppable.classList.remove('is-uploading');
    }

    async #fetchRequest(endpoint, params) {
        const data = new FormData();
        data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
        for (const prop in params) {
            data.append(prop, params[prop]);
        }

        const resp = await fetch(
            '/adm/file-manager/ajax/' + endpoint,
            {
                method: 'post',
                body: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );
        const json = await resp.json();
        return json;
    }

    // Drag & Drop event handlers
    #dragdropPrevent(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    #dragdropOverEnter(e) {
        this.#uploadFilesForm.classList.add('is-dragover');
    }
    #fileListDragDropOverEvent(e) {
        // If dragging from a folder, this will contain 'Files'
        if (!e.dataTransfer.types.includes('Files')) { return; }
        // If in Chrome, the types will just be ['Files'] - if Firefox, this will be ['application/x-moz-file', 'Files']
        if (
            (e.dataTransfer.types.includes('application/x-moz-file') && e.dataTransfer.types.length > 2) ||
            (!e.dataTransfer.types.includes('application/x-moz-file') && e.dataTransfer.types.length > 1)
        ) { return; }
        
        clearTimeout(this.#fileListDroppableTimer);
        this.#filesWrap.classList.add('is-dragover');
    }
    #dragdropLeaveEndDrop(e) {
        this.#uploadFilesForm.classList.remove('is-dragover');
    }
    #fileListDragDropLeaveEndEvent(e) {
        this.#fileListDroppableTimer = setTimeout(() => {
            this.#filesWrap.classList.remove('is-dragover');
        }, 100);
    }
    #dragdropDrop(e) {
        this.#uploadDroppedFiles = e.dataTransfer.files;
        this.#dragDropShowFiles();
        this.#uploadFilesForm.dispatchEvent(new Event('submit', {cancelable: true}));
    }
    #fileListDragDropDrop(e) {
        this.#uploadDroppedFiles = e.dataTransfer.files;
        this.#dragDropShowFiles();
        this.#uploadFilesModal.classList.add('open');
        this.#uploadFilesForm.dispatchEvent(new Event('submit', {cancelable: true}));
    }
    #dragDropShowFiles() {
        const filesCount = this.#uploadDroppedFiles.length;
        const upFilesLabel = this.#uploadFilesForm.querySelector('[data-uf-label]');
        const selectedLabel = upFilesLabel.querySelector('[data-selected-label]');
        const msg = filesCount > 1 ? (selectedLabel.dataset.multipleCaption).replace('{count}', filesCount) : this.#uploadDroppedFiles[0].name;

        selectedLabel.innerText = msg;
        selectedLabel.classList.add('selected');
    }
    #dragDropHideFiles() {
        const upFilesLabel = this.#uploadFilesForm.querySelector('[data-uf-label]');
        const selectedLabel = upFilesLabel.querySelector('[data-selected-label]');
        selectedLabel.classList.remove('selected');
        selectedLabel.innerText = '';
    }

    // Drag & drop move files & folders
    #listingTileDragDropFunctionBinds() {
        this.listingTileDragDropped = this.listingTileDragDropped.bind(this);
    }
    #bindListingTileDragDrop() {
        // Make tiles draggable
        for (const listingTile of this.#filesList.querySelectorAll('.listing-tile')) {
            listingTile.addEventListener('dragstart', this.#listingTileDragStart.bind(this));
            listingTile.addEventListener('dragend', (e) => {
                const dragovers = this.#wrapperEl.querySelectorAll('.folder-link.dragover, .tile-link.dragover');
                for (const dragover of dragovers) {
                    dragover.classList.remove('dragover');
                }
            });
        }

        // Allow drop on folders in the list
        for (const listingFolder of this.#filesList.querySelectorAll('.listing-tile.folder')) {
            listingFolder.addEventListener('drop', this.listingTileDragDropped);
            listingFolder.addEventListener('dragover', this.#listingTileDragover);
            listingFolder.addEventListener('dragleave', this.#listingTileDragleave);
        }
    }
    #bindSidebarFolderDragDrop() {
        // Allow drop on folders in the sidebar
        for (const sidebarFolder of this.#folderList.querySelectorAll('[data-folder-link]')) {
            sidebarFolder.addEventListener('drop', this.listingTileDragDropped);
            sidebarFolder.addEventListener('dragover', this.#listingTileDragover);
            sidebarFolder.addEventListener('dragleave', this.#listingTileDragleave);
        }
    }
    #listingTileDragStart(e) {
        e.stopPropagation();
        //console.log(e);

        const draggedEl = e.target;
        const draggedTile = draggedEl.closest('.listing-tile');
        const draggedLink = draggedTile.querySelector('.tile-link');

        let drag_resource_type = null;
        let drag_resource_id = 0;
        let drag_resource_title = null;
        if (draggedLink.hasAttribute('data-file-link')) {
            drag_resource_type = 'file';
            drag_resource_id = draggedLink.dataset.fileLink;
            drag_resource_title = draggedLink.dataset.title;
        }
        else if (draggedLink.hasAttribute('data-folder-link')) {
            drag_resource_type = 'folder';
            drag_resource_id = draggedLink.dataset.folderLink;
            drag_resource_title = draggedLink.dataset.title;
        }
        e.dataTransfer.setData('resourceType', drag_resource_type);
        e.dataTransfer.setData('resourceId', drag_resource_id);
        e.dataTransfer.setData('resourceTitle', drag_resource_title);

        this.#sidebar.addEventListener('dragover', this.#dragdropPrevent);
    }
    #listingTileDragover(e) {
        const dragoverTarget = e.target;
        if (
            dragoverTarget.classList.contains('folder-link') ||
            dragoverTarget.classList.contains('tile-link')
        ) {
            dragoverTarget.classList.add('dragover');  
        }
        else if (dragoverTarget.closest('.tile-link')) {
            dragoverTarget.closest('.tile-link').classList.add('dragover');
        }
    }
    #listingTileDragleave(e) {
        const dragleaveTarget = e.target;
        if (dragleaveTarget.classList.contains('dragover')) {
            dragleaveTarget.classList.remove('dragover');
        }
        else if (dragleaveTarget.closest('.tile-link')) {
            dragleaveTarget.closest('.tile-link').classList.remove('dragover');
        }
    }
    listingTileDragDropped(e) {
        // Prompt user to move file/folder into drop target
        const item_type = e.dataTransfer.getData('resourceType');
        const item_id = e.dataTransfer.getData('resourceId');
        const item_title = e.dataTransfer.getData('resourceTitle');

        let dest_title = null;
        let dest_id = 0;
        let dropTarget = e.target;
        if (!dropTarget.hasAttribute('data-folder-link')) {
            dropTarget = dropTarget.closest('[data-folder-link]');
        }
        if (dropTarget.hasAttribute('data-folder-link')) {
            dest_title = dropTarget.dataset.title;
            dest_id = dropTarget.dataset.folderLink;
        }

        if (item_type == 'folder' && item_id == dest_id) {
            dest_title = null;
        }

        if (dest_title !== null) {
            this.#confirmMoveModal.querySelector('[data-resource-type]').innerText = item_type;
            this.#confirmMoveModal.querySelector('[data-resource-title]').innerText = item_title;
            this.#confirmMoveModal.querySelector('[data-dest-title]').innerText = dest_title;

            this.#confirmMoveModal.querySelector('[data-submit-btn]').onclick = (submitEvt) => {
                submitEvt.preventDefault();

                this.#fetchRequest('move_item', {
                    item_type: item_type,
                    item_id: item_id,
                    dest_folder: dest_id
                })
                .then(json => {
                    if (json.success) {
                        this.#selectFolder(dest_id, dest_title);
                        this.#confirmMoveModal.classList.remove('open');
                    }
                });
            };

            this.#confirmMoveModal.classList.add('open');
        }

        this.#sidebar.removeEventListener('dragover', this.#dragdropPrevent);
    }

    // View file modal event handler
    #loadViewFileModal(file_id, show_loader) {
        if (!this.#viewFileModal) { return false; }

        if (show_loader) {
            this.#viewFileModal.querySelector('[data-modal-inner]').classList.add('loading');
        }

        this.#fetchRequest('load_file_details', { file: file_id })
            .then(json => {
                if (json.success) {
                    this.#viewFileModal.querySelector('[data-title] [data-value]').innerText = json.title;
                    this.#viewFileModal.querySelector('[data-file-size] [data-value]').innerText = json.file_size;
                    this.#viewFileModal.querySelector('[data-extension] [data-value]').innerText = json.extension;
                    this.#viewFileModal.querySelector('[data-uploaded] [data-value]').innerText = json.uploaded_date;
                    this.#viewFileModal.querySelector('[data-url] [data-value]').innerText = json.file_url;

                    const altField = this.#viewFileModal.querySelector('[data-alt-field]');
                    for (const fileField of this.#viewFileModal.querySelectorAll('[data-input="file_id"]')) {
                        fileField.value = json.file_id;
                    }

                    const dimensions = this.#viewFileModal.querySelector('[data-dimensions]');
                    if (json.is_image) {
                        dimensions.querySelector('[data-value]').innerText = json.dimensions;
                        dimensions.classList.remove('hidden');

                        altField.classList.remove('hidden');
                        altField.setAttribute('required', 'required');
                        this.#viewFileModal.querySelector('[data-view-link]').innerText = 'View image';
                    } else {
                        dimensions.querySelector('[data-value]').innerText = '';
                        dimensions.classList.add('hidden');

                        altField.classList.add('hidden');
                        altField.setAttribute('required', null);
                        this.#viewFileModal.querySelector('[data-view-link]').innerText = 'Open file';
                    }

                    this.#viewFileModal.querySelector('[data-view-link]').setAttribute('href', json.file_url);

                    this.#viewFileModal.querySelector('[data-input="title"]').value =json.title;
                    this.#viewFileModal.querySelector('[data-input="alt_text"]').value =json.alt_text;

                    const pane = this.#viewFileModal.querySelector('[data-file-pane]');
                    const modalImage = this.#viewFileModal.querySelector('[data-image-preview]');
                    const modalFile = this.#viewFileModal.querySelector('[data-file-preview]');
                    const modalControls = this.#viewFileModal.querySelector('[data-controls-wrap]');
                    if (modalControls) {
                        modalControls.classList.add('hidden');
                    }
                    if (json.is_image) {
                        modalFile.setAttribute('src', modalFile.dataset.src);
                        modalImage.setAttribute('src', json.image.src);
                        modalImage.setAttribute('alt', json.image.alt);
                        modalImage.setAttribute('width', json.image.width);
                        modalImage.setAttribute('height', json.image.height);
                        pane.classList.remove('pane-is-file', 'preview-unavailable');

                        if (json.is_editable && modalControls) {
                            modalControls.classList.remove('hidden');
                        }
                    } else {
                        modalImage.setAttribute('src', modalImage.dataset.src);
                        if (json.file_preview) {
                            modalFile.setAttribute('src', json.file_url);
                            pane.classList.add('pane-is-file');
                            pane.classList.remove('preview-unavailable');
                        } else {
                            modalFile.setAttribute('src', modalFile.dataset.src);
                            pane.classList.add('pane-is-file', 'preview-unavailable');
                        }
                    }

                    this.#viewFileModal.querySelector('[data-modal-inner]').classList.remove('loading');
                    this.#viewFileModal.classList.add('open');
                }
            });
    }
    #viewFileFlash(message, type) {
        if (!this.#viewFileModal) { return false; }

        for (const flash of this.#viewFileModal.querySelectorAll('[data-flash]')) {
            flash.classList.remove('open');
        }

        let flashBox = false;
        switch (type) {
            case 'error':
                flashBox = this.#viewFileModal.querySelector('[data-flash="error"]');
                break;
            case 'success':
            default:
                flashBox = this.#viewFileModal.querySelector('[data-flash="success"]');
                break;
        }
        if (flashBox) {
            flashBox.innerText = message;
            flashBox.classList.add('open');

            if (type == 'success') {
                setTimeout(() => {
                    flashBox.classList.remove('open');
                }, 2000);
            }
        }
    }
};

window.FileManagers = {};
window.addEventListener('load', () => {
    let fileManagerEl = document.querySelector('[data-file-manager]');
    if (fileManagerEl) {
        window.FileManagers[fileManagerEl.dataset.fileManager] = new FileManager(fileManagerEl);
    }
});

window.getFileManager = (fm_id) => {
    if (typeof(window.FileManagers[fm_id]) != 'undefined') {
        return window.FileManagers[fm_id];
    }

    return false;
}