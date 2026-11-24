import { bindCMSFields, changeFieldPrefixes } from '../generic/cms-fields';
import Sortable from 'sortablejs';

export function bindPageBuilder(wrapper) {
    const PbObj = new PageBuilder(wrapper);
}

class PageBuilder {
    #wrapperEl;
    #blockWrap;
    #blockSortable;
    #addBtn;
    #hasMax = false;
    #max = 0;

    #modal;
    #modalLoader;
    #modalBlockList;
    #modalBlockFilter;

    #removeModal;

    constructor(wrapperEl) {
        this.#wrapperEl = wrapperEl;
        this.#blockWrap = this.#wrapperEl.querySelector('[data-blocks]');
        this.#addBtn = this.#wrapperEl.querySelector('[data-add-block]');

        const pb_id = this.#wrapperEl.dataset.id;
        this.#modal = document.querySelector('[data-modal="page-builder-' + pb_id + '"]');
        this.#modalLoader = this.#modal.querySelector('[data-loader]');
        this.#modalBlockList = this.#modal.querySelector('[data-block-wrapper]');
        this.#modalBlockFilter = this.#wrapperEl.dataset.blockFilter;

        if (wrapperEl.dataset.max) {
            this.#hasMax = true;
            this.#max = parseInt(wrapperEl.dataset.max);
        }

        this.#removeModal = document.querySelector('[data-modal="page-builder-remove-' + pb_id + '"]');

        this.#addBtn.onclick = (e) => {
            e.preventDefault();
            this.#loadBlockListings(this.#addBtn.dataset.settings);
        };

        this.#blockSortable = new Sortable(this.#blockWrap, {
            animation: 150,
            handle: '[data-block-handle]',
            onUpdate: (evt) => {
                this.#reindexBlocks(evt.item);
            }
        });
        
        for (const block of this.#blockWrap.querySelectorAll('[data-block]')) {
            this.#bindBlockControls(block);
        }
    }

    #loadBlockListings(settings = null) {
        this.#modal.classList.add('open');

        const currentModalFilter = 'blockFilter' in this.#modalBlockList.dataset ? this.#modalBlockList.dataset.blockFilter : null;
        if (currentModalFilter === null || this.#modalBlockFilter != currentModalFilter) {
            this.#modalLoader.classList.remove('hidden');
            this.#modalBlockList.innerHTML = '';

            this.#jsonRequest('page_builder', 'loadBlockListing', {filter: this.#modalBlockFilter, settings: settings })
                .then(json => {
                    for (const block of json.blocks) {
                        let block_preview = '';
                        if (block.preview) {
                            block_preview = `
                                <div class="image-wrap">
                                    <div class="image-wrap-inner">
                                        <img
                                            src="${block.preview.src}" alt="${block.preview.alt}"
                                            width="${block.preview.width}" height="${block.preview.height}"
                                            loading="lazy"
                                        />
                                    </div>
                                </div>
                            `;
                        }
                        const block_html = `
                            <div class="w-full md:w-1/3 px-4 mb-4">
                                <a data-block-listing="${block.class}" href="#" class="modal-block-listing">
                                    ${block_preview}
                                    <div class="content-wrap">
                                        <p class="content-title">${block.title}</p>
                                        <p class="content-desc">${block.description}</p>
                                    </div>
                                </a>
                            </div>
                        `;
                        const listingBlock = new DOMParser().parseFromString(block_html, 'text/html').body.childNodes[0];
                        this.#modalBlockList.append(listingBlock);
                    }

                    this.#modalLoader.classList.add('hidden');
                    this.#modalBlockList.setAttribute('data-block-filter', this.#modalBlockFilter);

                    this.#bindBlockListings(settings);
                });
        }
    }

    #bindBlockListings() {
        for (const listingBlock of this.#modalBlockList.querySelectorAll('[data-block-listing]')) {
            listingBlock.onclick = (e) => {
                e.preventDefault()

                let requestParams = {
                    field_name: this.#wrapperEl.dataset.pageBuilder,
                    item_class: this.#wrapperEl.dataset.itemClass,
                    class: listingBlock.dataset.blockListing,
                    idx: this.#blockWrap.querySelectorAll('[data-block]').length
                };
                const previewPane = document.querySelector('[data-preview-pane]');
                if (previewPane) {
                    requestParams.visual_editor = 1;
                }
                this.#htmlRequest('page_builder', 'renderBlock', requestParams)
                    .then(html => {
                        const newBlock = new DOMParser().parseFromString(html, 'text/html').body.childNodes[0];
                        this.#bindBlockControls(newBlock);
                        this.#blockWrap.append(newBlock);
                        bindCMSFields(newBlock);
                        this.#modal.classList.remove('open');
                        document.dispatchEvent(new Event('page-builder-update'));
                        
                        if (this.#hasMax) {
                            if (this.#blockWrap.querySelectorAll('[data-block]').length >= this.#max) {
                                this.#addBtn.classList.add('hidden');
                            }
                        }
                    });
            };
        }
    }

    #bindBlockControls(block) {
        const removeBtn = block.querySelector('[data-remove-block]');
        const toggleBtn = block.querySelector('[data-toggle-status]');
        const expandCollapse = block.querySelector('[data-collapse]');

        removeBtn.onclick = (e) => {
            e.preventDefault();

            const removeModalConf = this.#removeModal.querySelector('[data-confirm-btn]');
            removeModalConf.onclick = (e) => {
                e.preventDefault();
                block.remove();
                this.#removeModal.classList.remove('open');
                this.#reindexBlocks();

                if (this.#hasMax) {
                    if (this.#blockWrap.querySelectorAll('[data-block]').length < this.#max) {
                        this.#addBtn.classList.remove('hidden');
                    }
                }
            }
            this.#removeModal.classList.add('open');
        };

        toggleBtn.onclick = (e) => {
            e.preventDefault();
            const inpt = toggleBtn.querySelector('[data-status-input]');

            if (inpt.value == "1") {
                inpt.value = 0;
                block.classList.add('inactive');
            } else {
                inpt.value = 1;
                block.classList.remove('inactive');
            }
        }

        if (expandCollapse) {
            expandCollapse.onclick = (e) => {
                e.preventDefault();

                block.classList.toggle('open');
            };
        }

        const commonBlockField = block.querySelector(
            'select[data-relation-field="Modules\\\\GlobalBlocks\\\\Models\\\\CommonBlock"]'
        );
        if(commonBlockField) {
            commonBlockField.onchange = (e) => {
                const blockTitle = block.querySelector('[data-block-title]');
                if(blockTitle) {
                    blockTitle.innerText = commonBlockField.value ? commonBlockField.selectedOptions[0].text : 'Common Block';
                }

                const commonBlockLink = block.querySelector('[data-common-block-link ]');
                if(commonBlockLink && commonBlockLink.dataset.commonBlockLink) {
                    commonBlockLink.href = commonBlockLink.dataset.commonBlockLink + '/' + commonBlockField.value;
                }
            }
        }
    }

    #reindexBlocks(moved_block) {
        const field_name = this.#wrapperEl.dataset.pageBuilder;

        if (typeof tinymce != 'undefined') {
            tinymce.triggerSave();
        }
        if (moved_block) {
            for (const moved_wysiwyg of moved_block.querySelectorAll('[data-wysiwyg]')) {
                const editor = tinymce.get(moved_wysiwyg.getAttribute('id'));
                if (editor) {
                    tinymce.remove(editor);
                }
            }
        }

        let block_idx = 0;
        for (const block of this.#blockWrap.querySelectorAll('[data-block]')) {
            changeFieldPrefixes(block, `${field_name}[${block.dataset.idx}]`, `${field_name}[${block_idx}]`)
            block.setAttribute('data-idx', block_idx);

            block_idx++;
        }

        if (typeof (window.loadWysiwygs) != 'undefined') {
            window.loadWysiwygs();
        }

        document.dispatchEvent(new Event('page-builder-update'));
    }

    async #jsonRequest(field, method, params) {
        const resp = await this.#fetchRequest(field, method, params);
        return resp.json();
    }

    async #htmlRequest(field, method, params) {
        const resp = await this.#fetchRequest(field, method, params);
        return resp.text();
    }

    async #fetchRequest(field, method, params) {
        const data = new FormData();
        data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
        data.append('field', field);
        data.append('method', method);
        for (const prop in params) {
            data.append(prop, params[prop]);
        }

        return fetch(
            '/adm/ajax/tools/field_method',
            {
                method: 'post',
                body: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );
    }
}