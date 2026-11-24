export function bindFileField(fieldWrap) {
    const hiddenInpt = fieldWrap.querySelector('[data-file-input]');
    const pathInpt = fieldWrap.querySelector('[data-file-path-input]');
    const browseBtn = fieldWrap.querySelector('[data-file-browse]');
    const removeBtn = fieldWrap.querySelector('[data-remove-btn]');

    const imageFileManager = window.getFileManager('modal');
    const imageFmModal = document.querySelector('[data-modal="image-file-manager"]');

    browseBtn.onclick = (e) => {
        e.preventDefault();

        if (imageFileManager && imageFmModal) {
            imageFileManager.initWithTarget(fieldWrap, 'file', 'file', fieldWrap.dataset.file, fieldWrap.dataset.folder);
            imageFmModal.classList.add('open');
        }
    };

    removeBtn.onclick = (e) => {
        e.preventDefault();
        
        hiddenInpt.value = '';
        pathInpt.value = '';

        fieldWrap.setAttribute('data-file', '');
        fieldWrap.setAttribute('data-folder', '');
    };

    fieldWrap.addEventListener('file-selected', (evt) => {
        if (evt.detail && evt.detail.file) {
            const selected_file_id = parseInt(evt.detail.file);

            const data = new FormData();
            data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
            data.append('field', 'file');
            data.append('method', 'getFileDetailsForRender');
            data.append('file', selected_file_id);

            fetch(
                '/adm/ajax/tools/field_method',
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
                hiddenInpt.value = json.file_id;
                pathInpt.value = json.file_path;

                fieldWrap.setAttribute('data-file', json.file_id);
                fieldWrap.setAttribute('data-folder', JSON.stringify({ id: json.folder_id, title: json.folder_title }));

                imageFmModal.classList.remove('open');
            });
        }
    });
};