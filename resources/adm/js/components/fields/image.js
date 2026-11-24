import Cropper from 'cropperjs';

export function bindImageField(imageTile) {
    const tileSelect = imageTile.querySelector('[data-tile-select]');
    const placeholder = imageTile.querySelector('[data-placeholder]');
    const preview = imageTile.querySelector('[data-preview]');
    const previewImg = preview.querySelector('img');

    const editBtn = imageTile.querySelector('[data-edit-image]');
    const removeBtn = imageTile.querySelector('[data-remove-image]');

    const imageFileManager = window.getFileManager('modal');
    const imageFmModal = document.querySelector('[data-modal="image-file-manager"]');

    tileSelect.onclick = (e) => {
        e.preventDefault();

        if (imageFileManager && imageFmModal) {
            imageFileManager.initWithTarget(imageTile, 'image', 'image', tileSelect.dataset.file, tileSelect.dataset.folder);
            imageFmModal.classList.add('open');
        }
    };

    removeBtn.addEventListener('click', (e) => {
        e.preventDefault();

        // Remove the file ID
        imageTile.querySelector('[data-file-id-input]').value = '';
        imageTile.querySelector('[data-file-id-input]').dispatchEvent(new Event('change'));
        // And reset the crop area
        for (const cropInpt of imageTile.querySelectorAll('[data-crop-input]')) {
            cropInpt.value = '';
        }

        tileSelect.setAttribute('data-file', '');
        tileSelect.setAttribute('data-folder', '');

        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');

        toggleTileActions(imageTile);
    });

    editBtn.onclick = (e) => {
        e.preventDefault();
        loadCropModal(imageTile);
    };

    imageTile.addEventListener('image-selected', (evt) => {
        if (evt.detail && evt.detail.file) {
            // Load file into preview & hide placeholder
            const selected_file_id = parseInt(evt.detail.file);

            const data = new FormData();
            data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
            data.append('field', 'image');
            data.append('method', 'getImageDetailsForRender');
            data.append('file', selected_file_id);
            data.append('size', imageTile.dataset.size);
            data.append('details', imageTile.dataset.details);

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
                // Set the file ID
                imageTile.querySelector('[data-file-id-input]').value = json.file_id;
                imageTile.querySelector('[data-file-id-input]').dispatchEvent(new Event('change'));
                // And reset the crop area
                for (const cropInpt of imageTile.querySelectorAll('[data-crop-input]')) {
                    cropInpt.value = '';
                }

                tileSelect.setAttribute('data-file', json.file_id);
                tileSelect.setAttribute('data-folder', JSON.stringify({id: json.folder_id, title: json.folder_title}));

                previewImg.setAttribute('src', json.image.src);
                previewImg.setAttribute('alt', json.image.alt);
                previewImg.setAttribute('width', json.image.width);
                previewImg.setAttribute('height', json.image.height);

                placeholder.classList.add('hidden');
                preview.classList.remove('hidden');

                toggleTileActions(imageTile);

                imageFmModal.classList.remove('open');
            });
        }
    });

    toggleTileActions(imageTile);
}

function toggleTileActions(imageTile) {
    if (imageTile.querySelector('[data-file-id-input]').value) {
        imageTile.querySelector('[data-edit-image]').classList.remove('hidden');
        imageTile.querySelector('[data-remove-image]').classList.remove('hidden');
    } else {
        imageTile.querySelector('[data-edit-image]').classList.add('hidden');
        imageTile.querySelector('[data-remove-image]').classList.add('hidden');
    }
}

function loadCropModal(imageTile) {
    const cropFileModal = document.querySelector('[data-modal="image-crop-modal"]');
    const file_id = imageTile.querySelector('[data-file-id-input]').value;
    if (!cropFileModal || !file_id) { return false; }

    cropFileModal.querySelector('[data-loader]').classList.remove('hidden');
    cropFileModal.classList.add('open');

    const cropWrap = cropFileModal.querySelector('[data-image-crop-wrapper]');
    const cropCanvas = cropWrap.querySelector('cropper-canvas');
    const previewImg = cropWrap.querySelector('[data-preview-image]');

    const saveBtn = cropFileModal.querySelector('[data-submit-btn]');
    const closeBtn = cropFileModal.querySelector('[data-close-btn]');

    const current_crop = {
        width: getTileCropValue(imageTile, 'w'),
        height: getTileCropValue(imageTile, 'h'),
        x: getTileCropValue(imageTile, 'x'),
        y: getTileCropValue(imageTile, 'y'),
        rotate: 0,
        scaleX: 1,
        scaleY: 1
    };

    const data = new FormData();
    data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
    data.append('field', 'image');
    data.append('method', 'getImageCropModal');
    data.append('file', file_id);
    data.append('size', imageTile.dataset.size);
    data.append('details', imageTile.dataset.details);

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
        if (json.success) {
            let cropInstance;

            const imageDestDetails = JSON.parse(imageTile.dataset.details);
            const dest_w = imageDestDetails.width;
            const dest_h = imageDestDetails.height;
            const aspect = dest_w && dest_h ? (dest_w / dest_h) : 'NaN';
            let imageScale;

            let preCropData = null;
            if (current_crop.width > -1 && current_crop.height > -1) {
                preCropData = current_crop;
            }

            cropInstance = new Cropper(previewImg, {
                template: `
                <cropper-canvas background>
                    <cropper-image></cropper-image>
                    <cropper-shade></cropper-shade>
                    <cropper-selection movable resizable outlined>
                        <cropper-handle plain action="move"></cropper-handle>
                        <cropper-handle plain action="ne-resize"></cropper-handle>
                        <cropper-handle plain action="nw-resize"></cropper-handle>
                        <cropper-handle plain action="se-resize"></cropper-handle>
                        <cropper-handle plain action="sw-resize"></cropper-handle>
                    </cropper-selection>
                </cropper-canvas>
            `
            });
            window.cropInstance = cropInstance;

            const cropCanv = cropInstance.getCropperCanvas();
            const cropImg = cropInstance.getCropperImage();
            const cropSel = cropInstance.getCropperSelection();

            const calcXOffset = () => {
                const canvRect = cropCanv.getBoundingClientRect();
                const imgRect = cropImg.getBoundingClientRect();
                return imgRect.left - canvRect.left;
            }
            const calcYOffset = () => {
                const canvRect = cropCanv.getBoundingClientRect();
                const imgRect = cropImg.getBoundingClientRect();
                return imgRect.top - canvRect.top;
            }

            cropImg.$ready(() => {
                const canvRect = cropCanv.getBoundingClientRect();
                const imgRect = cropImg.getBoundingClientRect();
                imageScale = json.width / imgRect.width;

                if (current_crop.width > -1 && current_crop.height > -1) {
                    cropSel.x = calcXOffset() + (current_crop.x / imageScale);
                    cropSel.y = calcYOffset() + (current_crop.y / imageScale);
                    cropSel.width = current_crop.width / imageScale;
                    cropSel.height = current_crop.height / imageScale;
                } else {
                    cropSel.x = calcXOffset();
                    cropSel.y = calcYOffset();
                    cropSel.width = imgRect.width;
                    cropSel.height = imgRect.width / aspect;
                    cropSel.$center();
                }

                cropFileModal.querySelector('[data-loader]').classList.add('hidden');
            });

            cropSel.initialAspectRatio = aspect;
            cropSel.aspectRatio = aspect;

            cropSel.addEventListener('change', (evt) => {
                const canvRect = cropCanv.getBoundingClientRect();
                const imgRect = cropImg.getBoundingClientRect();
                const selection = evt.detail;
                const maxSelection = {
                    x: Math.floor(imgRect.left - canvRect.left),
                    y: Math.floor(imgRect.top - canvRect.top),
                    width: Math.ceil(imgRect.width),
                    height: Math.ceil(imgRect.height)
                }

                const inSelection = (selection, maxSelection) => {
                    return (
                        selection.x >= maxSelection.x
                        && selection.y >= maxSelection.y
                        && (selection.x + selection.width) <= (maxSelection.x + maxSelection.width)
                        && (selection.y + selection.height) <= (maxSelection.y + maxSelection.height)
                    );
                }

                if (!inSelection(selection, maxSelection)) {
                    evt.preventDefault();
                }
            });

            cropImg.src = json.src;

            saveBtn.onclick = (e) => {
                e.preventDefault();

                const cropData = {
                    width: parseInt(cropSel.width * imageScale),
                    height: parseInt(cropSel.height * imageScale),
                    x: parseInt((cropSel.x - calcXOffset()) * imageScale),
                    y: parseInt((cropSel.y - calcYOffset()) * imageScale)
                }
                setTileCropValue(imageTile, 'w', cropData.width);
                setTileCropValue(imageTile, 'h', cropData.height);
                setTileCropValue(imageTile, 'x', cropData.x);
                setTileCropValue(imageTile, 'y', cropData.y);

                const data = new FormData();
                data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
                data.append('field', 'image');
                data.append('method', 'getCroppedImageSource');
                data.append('file', file_id);
                data.append('size', imageTile.dataset.size);
                data.append('details', imageTile.dataset.details);
                data.append('crop', JSON.stringify(cropData));

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
                    imageTile.querySelector('[data-preview] img').setAttribute('src', json.src);
                    imageTile.querySelector('[data-file-id-input]').dispatchEvent(new Event('change'));
                });

                cropInstance.destroy();
                cropFileModal.classList.remove('open');
            };

            closeBtn.onclick = (e) => {
                e.preventDefault();
                cropInstance.destroy();
                cropFileModal.classList.remove('open');
            };
        } else {
            cropFileModal.classList.remove('open');
        }
    });
}

function getTileCropValue(imageTile, cropDimension) {
    const inpt = imageTile.querySelector('[data-crop-input="' + cropDimension + '"]');
    if (!inpt) { return -1; }

    const inpt_value = inpt.value;
    return inpt_value.length > 0 ? parseInt(inpt_value) : -1;
}

function setTileCropValue(imageTile, cropDimension, newValue) {
    const inpt = imageTile.querySelector('[data-crop-input="' + cropDimension + '"]');
    if (!inpt) { return; }

    inpt.value = newValue;
}