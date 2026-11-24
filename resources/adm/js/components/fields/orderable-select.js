import Sortable, { MultiDrag, Swap } from 'sortablejs';

export function bindSortable(fieldWrap) {
    const unselectedList = fieldWrap.querySelector('[data-unselected-list]');
    const selectedList = fieldWrap.querySelector('[data-selected-list]');

    const removeInputsFromItem = (optItem) => {
        for (const inpt of optItem.querySelectorAll('input')) { inpt.remove(); }
        setTimeout(() => { document.dispatchEvent(new Event('page-builder-update')) }, 1000);
    };
    const addInputToItem = (optItem) => {
        removeInputsFromItem(optItem);

        const newInpt = document.createElement('input');
        newInpt.setAttribute('type', 'hidden');
        newInpt.setAttribute('name', fieldWrap.dataset.field);
        newInpt.value = optItem.dataset.value;

        optItem.appendChild(newInpt);
        setTimeout(() => { document.dispatchEvent(new Event('page-builder-update')) }, 1000);
    };

    const unselectedSort = new Sortable(unselectedList, {
        group: fieldWrap.dataset.orderableSelect,
        animation: 150,
    });
    new Sortable(selectedList, {
        group: fieldWrap.dataset.orderableSelect,
        animation: 150,
        onAdd: (evt) => {
            const optItem = evt.item;
            addInputToItem(optItem);
        },
        onRemove: (evt) => {
            const optItem = evt.item;
            removeInputsFromItem(optItem);
        }
    });

    const populateOptionsFromJson = (json) => {
        let existing_values = [];
        for (const option of fieldWrap.querySelectorAll('[data-value]')) {
            existing_values.push(option.dataset.value);
        }

        if (json.success && json.opts) {
            let all_values = [];
            for (let option_id in json.opts) {
                const thisOpt = json.opts[option_id];
                let opt_label = json.opts[option_id];
                if (typeof thisOpt != 'string') {
                    option_id = thisOpt.id;
                    opt_label = thisOpt.label;
                }
                all_values.push(option_id);

                // If the value is already there, make sure it's title is up to date
                if (existing_values.includes(option_id) || existing_values.includes('' + option_id)) {
                    if (option_id > 0) {
                        const existingOption = fieldWrap.querySelector('[data-value="' + option_id + '"]');
                        if (existingOption) {
                            existingOption.querySelector('.label').innerText = opt_label;
                        }
                    }
                    continue;
                }

                // Otherwise, it's a new value - so insert it at the top
                const opt_html = `<div data-value="${option_id}" class="option"><div class="label">${opt_label}</div></div>`;
                const opt_dom = new DOMParser().parseFromString(opt_html, 'text/html').body.childNodes[0];

                if (json.init) {
                    const newInpt = document.createElement('input');
                    newInpt.setAttribute('type', 'hidden');
                    newInpt.setAttribute('name', fieldWrap.dataset.field);
                    newInpt.value = option_id;
                    opt_dom.appendChild(newInpt);
                    selectedList.appendChild(opt_dom);
                } else {
                    unselectedList.prepend(opt_dom);
                }
            }

            // And if a related item has been deleted, remove it from the dropdown
            for (const option of fieldWrap.querySelectorAll('[data-value]')) {
                if (!all_values.includes(parseInt(option.dataset.value))) {
                    option.remove();
                }
            }
        }
    };

    const searchInpt = fieldWrap.querySelector('[data-option-search]');
    if (searchInpt) {
        let searchTimer;
        searchInpt.onkeyup = (e) => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const searchPlaceholder = fieldWrap.querySelector('[data-search-placeholder]');
                if (searchPlaceholder && !e.detail.init) {
                    searchPlaceholder.classList.add('hidden');
                }

                const search_term = searchInpt.value;
                if (fieldWrap.hasAttribute('data-lazy')) {
                    let existing_values = [];
                    for (const option of selectedList.querySelectorAll('[data-value]')) {
                        existing_values.push(option.dataset.value);
                    }

                    let selected = existing_values.length ? existing_values : JSON.parse(fieldWrap.dataset.selected);

                    const data = new FormData();
                    data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
                    data.append('field', 'orderableSelect');
                    data.append('method', 'searchRelatedOptions');
                    data.append('relation_class', fieldWrap.dataset.related);
                    data.append('search', search_term);
                    data.append('selected', JSON.stringify(selected));
                    data.append('init', e.detail?.init);

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
                        for (const unselectedOpt of unselectedList.querySelectorAll('[data-value]')) {
                            unselectedOpt.remove();
                        }
                        populateOptionsFromJson(json);
                    })
                    .catch((err) => {});
                } else {
                    for (const option of unselectedList.querySelectorAll('.option')) {
                        if (!search_term.length) {
                            option.classList.remove('hidden');
                        } else {
                            if (option.innerText.toLowerCase().includes(search_term.toLowerCase())) {
                                option.classList.remove('hidden');
                            } else {
                                option.classList.add('hidden');
                            }
                        }
                    }
                }
            }, 200);
        };
        if (fieldWrap.hasAttribute('data-lazy')) {
            searchInpt.dispatchEvent(new CustomEvent('keyup', { detail: {
                init: true
            }}));
        }
    }

    const moveAllButtons = fieldWrap.querySelectorAll('[data-move-all]');
    if (moveAllButtons.length) {
        for (const moveAllButton of moveAllButtons) {
            moveAllButton.onclick = (e) => {
                e.preventDefault();

                switch (moveAllButton.dataset.moveAll) {
                    case 'unselected':
                        for (const item of unselectedList.querySelectorAll('[data-value]')) {
                            const newItem = item.cloneNode(true);
                            addInputToItem(newItem);
                            selectedList.append(newItem);
                            item.remove();
                        }
                        break;
                    case 'selected':
                        for (const item of selectedList.querySelectorAll('[data-value]')) {
                            const newItem = item.cloneNode(true);
                            removeInputsFromItem(newItem);
                            unselectedList.append(newItem);
                            item.remove();
                        }
                        break;
                }
            };
        }
    }

    if (fieldWrap.hasAttribute('data-related')) {
        const channel_name = fieldWrap.getAttribute('data-related').replaceAll('\\', '-');
        const fieldBroadcast = new BroadcastChannel(channel_name);
        fieldBroadcast.onmessage = (e) => {
            const class_name = fieldBroadcast.name.replaceAll('-', '\\');

            const data = new FormData();
            data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
            data.append('field', 'orderableSelect');
            data.append('method', 'fetchRelatedOptions');
            data.append('relation_class', class_name);
            data.append('relation_config', fieldWrap.dataset.relationConfig);

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
                populateOptionsFromJson(json);
            })
            .catch((err) => { });
        };
    }
}