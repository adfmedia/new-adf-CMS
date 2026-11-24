import Sortable from 'sortablejs';
import { changeFieldPrefixes } from './components/generic/cms-fields';

class MenuBuilder {
    #builder;
    #linkTypes;
    #structure;

    #structureSortable;
    #structureLoader;

    #removeItemModal;

    constructor(wrapper) {
        this.#builder = wrapper;
        this.#linkTypes = this.#builder.querySelectorAll('[data-menu-link-type]');
        this.#structure = this.#builder.querySelector('[data-structure]');
        this.#structureLoader = this.#builder.querySelector('[data-structure-loader]');
        this.#removeItemModal = document.querySelector('[data-modal="menu-builder-remove-item"]');

        this.#initSortables();
        this.#bindStructureItemEvents(this.#builder);
        this.#bindExternalLink();
        this.#loadLinks();
        this.#bindEditModal();
    }

    #loadLinks() {
        let types = [];
        for (const linkType of this.#linkTypes) {
            types.push(linkType.dataset.menuLinkType);
        }

        this.#jsonRequest('getLinks', {types: types})
            .then(json => {
                for (const type_class in json.links) {
                    let linkType;
                    for (const searchType of this.#linkTypes) {
                        if (searchType.dataset.menuLinkType == type_class) {
                            linkType = searchType;
                        }
                    }
                    if (linkType) {
                        const inner = linkType.querySelector('[data-inner-links]');
                        for (const linkIdx in json.links[type_class]) {
                            const typeLink = json.links[type_class][linkIdx];
                            const opt_html = `
                                <div
                                    data-link
                                    data-type="${type_class}" data-url="${typeLink.url_id}"
                                    class="p-2 bg-white border border-gray-300 mb-2 cursor-grab"
                                >${typeLink.title}</div>
                            `;
                            const optDom = new DOMParser().parseFromString(opt_html, 'text/html').body.childNodes[0];
                            inner.append(optDom);
                        }
                    }
                }
            });
    }

    #initSortables() {
        this.#structureSortable = new Sortable(this.#structure, this.#getStructureSortableOptions());

        for (const linkType of this.#linkTypes) {
            new Sortable(linkType.querySelector('[data-inner-links]'), {
                group: {
                    name: 'menu-builder',
                    pull: 'clone',
                    put: false
                },
                sort: false,
                animation: 150
            })
        }
    }

    #bindStructureItemEvents(wrapper) {
        for (const linkWrap of wrapper.querySelectorAll('[data-item-link-wrap]')) {
            const ddToggle = linkWrap.querySelector('[data-dd-toggle]');
            ddToggle.onclick = (e) => {
                e.preventDefault();
                linkWrap.classList.toggle('expanded');
            };

            const collapseToggle = linkWrap.querySelector('[data-collapse-toggle]');
            if (collapseToggle) {
                collapseToggle.onclick = (e) => {
                    e.preventDefault();
                    linkWrap.classList.toggle('collapsed');
                }
            }

            const titleInput = linkWrap.querySelector('[data-title-input]');
            titleInput.onchange = () => {
                const parent = titleInput.closest('[data-item-link-wrap]');
                parent.querySelector('[data-title-label]').innerText = titleInput.value;
            };

            const advancedWrap = linkWrap.querySelector('[data-advanced-wrap]');
            if (advancedWrap) {
                const toggle = advancedWrap.querySelector('[data-adv-dd-toggle]');
                toggle.onclick = (e) => {
                    e.preventDefault();
                    advancedWrap.classList.toggle('expanded');
                };
            }

            const removeBtn = linkWrap.querySelector('[data-remove-item]');
            removeBtn.onclick = (e) => {
                e.preventDefault();

                this.#removeItemModal.querySelector('[data-confirm-btn]').onclick = (e2) => {
                    e2.preventDefault();

                    const parent = linkWrap.closest('[data-structure-item]');
                    parent.remove();

                    // Trigger drop event to reindex
                    this.#structureItemDropped(parent);

                    this.#removeItemModal.classList.remove('open');
                };
                this.#removeItemModal.classList.add('open');
            };
        }
        for (const innerNest of wrapper.querySelectorAll('[data-item-nest]')) {
            new Sortable(innerNest, this.#getStructureSortableOptions(wrapper.dataset.depth));
        }
    }

    #getStructureSortableOptions(current_depth) {
        if (typeof current_depth == 'undefined' || !current_depth) { current_depth = 0; }

        if (current_depth >= this.#builder.dataset.nesting) {
            return {
                group: {
                    name: 'menu-builder',
                    put: false
                },
                handle: '[data-handle]',
                animation: 150
            };
        }

        return {
            group: 'menu-builder',
            handle: '[data-handle]',
            animation: 150,
            onAdd: (evt) => {
                const itemAdded = evt.item;
                this.#structureLoader.classList.remove('hidden');

                let linkParams = {
                    type: 'internal',
                    value: itemAdded.dataset.url,
                    depth: this.#getItemDepth(itemAdded),
                    prefix: this.#getFieldPrefix(itemAdded, evt.newIndex)
                };
                if (itemAdded.hasAttribute('data-structure-item')) {
                    linkParams.type = itemAdded.dataset.type;
                    linkParams.value = itemAdded.dataset.value;
                    linkParams.title = itemAdded.querySelector('[data-title-input]').value;
                }
                this.#htmlRequest('linkAdded', linkParams)
                    .then(html => {
                        const newItem = new DOMParser().parseFromString(html, 'text/html').body.childNodes[0];
                        itemAdded.replaceWith(newItem);
                        this.#structureLoader.classList.add('hidden');

                        this.#bindStructureItemEvents(newItem);
                        this.#structureItemDropped(newItem);
                    });
            },
            onUpdate: (evt) => {
                this.#structureItemDropped(evt.item);
            }
        };
    }

    #getItemDepth(item, depth) {
        if (typeof depth == 'undefined') { depth = 0; }

        const parentNest = item.closest('[data-item-nest]');
        if (parentNest) {
            depth++;
            const parentItem = parentNest.closest('[data-structure-item]');
            if (parentItem) {
                return this.#getItemDepth(parentItem, depth);
            }
            return depth;
        }

        return depth + 1;
    }

    #getFieldPrefix(item, itemIndex) {
        let prefix = 'links';

        const parentNest = item.closest('[data-item-nest]');
        if (parentNest) {
            prefix = parentNest.dataset.prefix;
        }

        return prefix + '[' + itemIndex + ']';
    }

    #getNextIndexAtDepth(depth, wrapper) {
        let idx = 0;
        for (const itemAtDepth of wrapper.querySelectorAll('[data-structure-item][data-depth="' + depth + '"]')) {
            idx++;
        }
        return idx;
    }

    #bindExternalLink() {
        const externalWrap = this.#builder.querySelector('[data-external-link-form]');
        const urlInpt = externalWrap.querySelector('[name="value"]');
        const titleInpt = externalWrap.querySelector('[name="title"]');
        const externalAddBtn = externalWrap.querySelector('[data-add-external]');
        externalAddBtn.onclick = (e) => {
            e.preventDefault();

            const valid = externalWrap.checkValidity();
            if (!valid) {
                externalWrap.reportValidity();
                return false;
            }

            let linkParams = {
                type: 'external',
                value: urlInpt.value,
                title: titleInpt.value,
                depth: 1,
                prefix: this.#getFieldPrefix(externalAddBtn, this.#getNextIndexAtDepth(1, this.#structure))
            };
            this.#htmlRequest('linkAdded', linkParams)
                .then(html => {
                    const newItem = new DOMParser().parseFromString(html, 'text/html').body.childNodes[0];
                    this.#structure.append(newItem);
                    this.#structureLoader.classList.add('hidden');

                    this.#bindStructureItemEvents(newItem);
                });
        };
    }

    #structureItemDropped(itemDropped) {
        const nest = itemDropped.closest('[data-item-nest]');

        const field = nest ? nest.dataset.prefix : 'links';
        const wrap = nest ? nest : this.#structure;

        this.#reindexLinks(wrap, field);
    }

    #reindexLinks(wrapper, field_name) {
        let idx = 0;
        for (const item of wrapper.querySelectorAll('[data-structure-item]')) {
            if (item.parentElement != wrapper) { continue; }

            const item_prefix = item.dataset.prefix;
            const new_prefix = `${field_name}[${idx}]`;
            changeFieldPrefixes(item, item_prefix, new_prefix);
            item.setAttribute('data-prefix', new_prefix);

            for (const nest of item.querySelectorAll('[data-item-nest]')) {
                if (nest.closest('[data-structure-item]') != item) { continue; }

                const nest_prefix = nest.dataset.prefix;
                nest.setAttribute('data-prefix', nest_prefix.replace(item_prefix, new_prefix));
                this.#reindexLinks(nest, nest.dataset.prefix);
            }

            idx++;
        }
    }

    #bindEditModal() {
        const editModal = document.querySelector('[data-modal="menu-builder-edit-details"]');
        if (editModal) {
            const openBtn = this.#builder.querySelector('[data-edit-menu]');

            openBtn.onclick = (e) => {
                e.preventDefault();
                editModal.classList.add('open');
            }

            editModal.querySelector('[data-confirm-btn]').onclick = (e) => {
                e.preventDefault();

                const form = editModal.querySelector('[data-edit-form]');
                const valid = form.checkValidity();
                if (!valid) {
                    form.reportValidity();
                    return false;
                }

                const postBody = new FormData(form);
                postBody.append('method', 'update_menu_details');

                fetch(
                    '/adm/ajax/tools/menu_method',
                    {
                        method: 'post',
                        body: postBody,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                )
                .then(resp => resp.json())
                .then(json => {
                    window.location.reload();
                });
            };
        }
    }

    async #jsonRequest(method, params) {
        const resp = await this.#fetchRequest(method, params);
        return resp.json();
    }

    async #htmlRequest(method, params) {
        const resp = await this.#fetchRequest(method, params);
        return resp.text();
    }

    async #fetchRequest(method, params) {
        const data = new FormData();
        data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
        data.append('method', method);
        data.append('menu_id', this.#builder.dataset.menu);
        for (const prop in params) {
            if (typeof params[prop] == 'object') {
                for (const subprop in params[prop]) {
                    data.append(`${prop}[${subprop}]`, params[prop][subprop]);
                }
            } else {
                data.append(prop, params[prop]);
            }
        }

        return fetch(
            '/adm/ajax/tools/menu_method',
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

window.addEventListener('load', () => {
    new MenuBuilder(document.querySelector('[data-menu-builder]'));
});