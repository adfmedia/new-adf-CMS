export function bindLinkField(fieldWrap) {
    const fieldInput = fieldWrap.querySelector('[data-link-input]');
    const browseBtn = fieldWrap.querySelector('[data-link-browse]');

    if (fieldInput) {
        const resultsWrap = fieldWrap.querySelector('[data-lookup-results-wrap]');
        const resultsLoader = resultsWrap.querySelector('[data-loader]');
        const resultsEl = resultsWrap.querySelector('[data-lookup-results]');

        let searchTimer;
        let searching = false;
        fieldInput.onkeyup = (e) => {
            clearTimeout(searchTimer);
            if (searching) { return; }

            searchTimer = setTimeout(() => {
                searching = true;
                resultsLoader.classList.remove('hidden');
                resultsEl.innerHTML = '';
                resultsWrap.classList.remove('hidden');

                const data = new FormData();
                data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
                data.append('search', fieldInput.value);

                fetch(
                    '/adm/ajax/tools/link_search',
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
                    if (json.links.length) {
                        for (const link of json.links) {
                            const opt_str = `
                                <div
                                    data-link-option data-value="${link.value}" 
                                    class="py-px px-1 mb-1 cursor-pointer transition-colors hover:bg-gray-200"
                                >${link.label}</div>
                            `;
                            const opt_dom = new DOMParser().parseFromString(opt_str, 'text/html').body.childNodes[0];
                            resultsEl.append(opt_dom);
                        }

                        for (const linkOpt of resultsEl.querySelectorAll('[data-link-option]')) {
                            linkOpt.onclick = (e) => {
                                e.preventDefault();

                                fieldInput.value = linkOpt.getAttribute('data-value');
                                fieldInput.dispatchEvent(new Event('change'));

                                resultsWrap.classList.add('hidden');
                                resultsEl.innerHTML = '';
                            };
                        }
                    } else {
                        const empty_str = `<div class="text-center pt-4">No links found</div>`;
                        const empty_dom = new DOMParser().parseFromString(empty_str, 'text/html').body.childNodes[0];
                        resultsEl.append(empty_dom);
                    }

                    searching = false;
                    resultsLoader.classList.add('hidden');
                })
                .catch(() => {
                    searching = false;
                    resultsEl.innerHTML = '<div>No links found.</div>';
                    resultsLoader.classList.add('hidden');
                });
            }, 300);
        };

        const close = resultsWrap.querySelector('[data-close-results]');
        if (close) {
            close.onclick = (e) => {
                e.preventDefault();
                resultsWrap.classList.add('hidden');
            }
        }

        if (browseBtn) {
            const browseModal = document.querySelector('[data-modal="browse-links"]');
            if (browseModal) {
                browseBtn.onclick = (e) => {
                    e.preventDefault();

                    if (!browseModal.dataset.populated) {
                        populateBrowseModal(browseModal)
                            .finally(() => {
                                bindModalLinkClicks(fieldInput, browseModal);
                            });
                    } else {
                        bindModalLinkClicks(fieldInput, browseModal);
                        browseModal.classList.add('open');
                    }
                };

                browseBtn.classList.remove('hidden');
            }
        }
    }
};

async function populateBrowseModal(modal) {
    const loader = modal.querySelector('[data-loader]');
    loader.classList.remove('hidden');
    loader.classList.add('flex');
    modal.classList.add('open');

    const data = new FormData();
    data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
    data.append('field', 'link');
    data.append('method', 'getLinksForModal');

    return fetch(
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
        const linkWrap = modal.querySelector('[data-link-wrapper]');
        for (const group of json.link_groups) {
            let group_links = ``;
            for (const link of group.links) {
                group_links += `
                    <div
                        data-link-option data-value="${link.value}" 
                        class="p-1 rounded-md mb-1 cursor-pointer transition-colors hover:bg-gray-200"
                    >
                        <div class="text-primary-medium">${link.label}</div>
                        <div class="text-xs">${link.slug}</div>
                    </div>
                `;
            }
            const this_group = `
                <div class="pt-2">
                    <h3 class="text-xl font-semibold mb-2">${group.title}</h3>
                    ${group_links}
                </div>
            `;
            const group_dom = new DOMParser().parseFromString(this_group, 'text/html').body.childNodes[0];
            linkWrap.appendChild(group_dom);
        }

        modal.dataset.populated = true;
        loader.classList.add('hidden');
    });
}

function bindModalLinkClicks(input, modal) {
    for (const linkOpt of modal.querySelectorAll('[data-link-option]')) {
        linkOpt.onclick = (e) => {
            e.preventDefault();

            input.value = linkOpt.getAttribute('data-value');

            modal.classList.remove('open');
        };
    }

    const browseFiles = modal.querySelector('[data-browse-files]');
    if (browseFiles) {
        browseFiles.onclick = (e) => {
            e.preventDefault();

            const imageFileManager = window.getFileManager('modal');
            const imageFmModal = document.querySelector('[data-modal="image-file-manager"]');

            if (imageFileManager && imageFmModal) {
                imageFileManager.initWithTarget(input, 'file', 'file');
                modal.classList.remove('open');
                imageFmModal.classList.add('open');
            }

            input.addEventListener('file-selected', (evt) => {
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
                        input.value = json.file_path;
                        imageFmModal.classList.remove('open');
                    });
                }
            });
        }
    }
}