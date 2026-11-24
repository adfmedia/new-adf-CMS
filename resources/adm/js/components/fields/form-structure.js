import Sortable from 'sortablejs';
import { changeFieldPrefixes } from '../generic/cms-fields';

class FormStructure {
    #wrapper;
    #rowsWrap;

    #sortable_group;

    #addRow;
    #addField;
    #placeholders;

    constructor(wrapper) {
        this.#wrapper = wrapper;
        this.#rowsWrap = this.#wrapper.querySelector('[data-row-wrapper]');

        this.#sortable_group = this.#wrapper.dataset.formStructure;

        this.#addRow = this.#wrapper.querySelector('[data-add-row]');
        this.#addField = this.#wrapper.querySelector('[data-add-field]');
        this.#placeholders = document.querySelector('[data-form-structure-placeholders]');

        this.#initSortableWrapper();

        for (const field of this.#rowsWrap.querySelectorAll('[data-form-field]')) {
            this.#bindFieldEvents(field);
        }

        this.#bindAddRow();
        this.#bindAddField();
    }

    #initSortableWrapper() {
        new Sortable(this.#rowsWrap, {
            group: {
                name: (this.#sortable_group + '-row-wrap'),
                pull: false
            },
            handle: '[data-row-handle]',
            animation: 150,
            onAdd: (evt) => {

            },
            onUpdate: (evt) => {
                this.#setRowColumnAndFieldIndexes();
            }
        });
        this.#initSortableRows();
    }

    #initSortableRows() {
        for (const row of this.#rowsWrap.querySelectorAll('[data-structure-row]')) {
            this.#initSortableRow(row);
        }
    }
    #initSortableRow(row) {
        new Sortable(row.querySelector('[data-row-columns]'), {
            group: {
                name: (this.#sortable_group + '-rows'),
                pull: false
            },
            handle: '[data-col-handle]',
            direction: 'horizontal',
            animation: 150,
            onAdd: (evt) => {

            },
            onUpdate: (evt) => {
                this.#setRowColumnAndFieldIndexes();
            }
        });
        this.#bindRowActions(row);
        this.#initSortableColumns(row);
    }

    #initSortableColumns(wrapper) {
        for (const col of wrapper.querySelectorAll('[data-row-column]')) {
            this.#initSortableColumn(col);
        }
    }
    #initSortableColumn(column) {
        new Sortable(column.querySelector('[data-column-fields]'), {
            group: {
                name: (this.#sortable_group + '-cols')
            },
            handle: '[data-field-handle]',
            animation: 150,
            onAdd: (evt) => {
                this.#setRowColumnAndFieldIndexes();
            },
            onUpdate: (evt) => {
                this.#setRowColumnAndFieldIndexes();
            }
        });
        this.#bindColumnActions(column);
    }

    #bindRowActions(row) {
        const removeRow = row.querySelector('[data-remove-row]');
        const addCol = row.querySelector('[data-add-col]');

        removeRow.onclick = (e) => {
            e.preventDefault();

            if (this.#rowsWrap.querySelectorAll('[data-structure-row]').length > 1) {
                removeRow.closest('[data-structure-row]').remove();
                this.#setRowColumnAndFieldIndexes();
            }
        };

        addCol.onclick = (e) => {
            e.preventDefault();

            const row = addCol.closest('[data-structure-row]');
            const newCol = this.#getPlaceholderDom('column');
            row.querySelector('[data-row-columns]').appendChild(newCol);
            this.#initSortableColumn(newCol);
            this.#setRowColumnAndFieldIndexes();
        }
    }

    #bindColumnActions(column) {
        const removeCol = column.querySelector('[data-remove-col]');

        removeCol.onclick = (e) => {
            e.preventDefault();

            const col = removeCol.closest('[data-row-column]');
            const row = col.closest('[data-structure-row]');
            if (row.querySelectorAll('[data-row-column]').length > 1) {
                col.remove();
                this.#setRowColumnAndFieldIndexes();
            }
        }
    }

    #bindFieldActions(field) {
        this.#bindFieldEvents(field);
        this.#setRowColumnAndFieldIndexes();
    }
    #bindFieldEvents(field) {
        const toggle = field.querySelector('[data-field-toggle]');
        toggle.onclick = (e) => {
            e.preventDefault();

            field.classList.toggle('expanded');
        };

        const lblOutput = field.querySelector('[data-field-label]');
        const lblInpt = field.querySelector('[data-label-inpt]');
        lblInpt.onkeyup = () => {
            lblOutput.innerText = lblInpt.value.length ? lblInpt.value : 'New field';
        };

        const typOutput = field.querySelector('[data-field-type]');
        const typInpt = field.querySelector('[data-type-inpt]');
        const optionsWrap = field.querySelector('[data-field-options]');
        const optionalSettings = field.querySelectorAll('[data-field-optional-settings]');
        typInpt.onchange = () => {
            typOutput.innerText = typInpt.querySelector('option[value="' + typInpt.value + '"]').innerText;
            if (optionsWrap) {
                const optTypes = JSON.parse(optionsWrap.dataset.optionTypes);
                if (optTypes.includes(typInpt.value)) {
                    optionsWrap.classList.remove('hidden');
                } else {
                    optionsWrap.classList.add('hidden');
                }
            }

            if (optionalSettings.length) {
                for (const optionalSetting of optionalSettings) {
                    if (JSON.parse(optionalSetting.dataset.types).includes(typInpt.value)) {
                        optionalSetting.classList.remove('hidden');
                    } else {
                        optionalSetting.classList.add('hidden');
                    }
                }
            }
        };
        typInpt.dispatchEvent(new Event('change'));

        const removeField = field.querySelector('[data-remove-field]');
        if (removeField) {
            removeField.onclick = (e) => {
                e.preventDefault();
                field.remove();
                this.#setRowColumnAndFieldIndexes();
            };
        }
    }

    #setRowColumnAndFieldIndexes() {
        let row_idx = 0;
        for (const row of this.#rowsWrap.querySelectorAll('[data-structure-row]')) {
            row.setAttribute('data-structure-row', row_idx);

            let col_idx = 0;;
            for (const col of row.querySelectorAll('[data-row-column]')) {
                col.setAttribute('data-row-column', col_idx);

                let field_idx = 0;
                for (const field of col.querySelectorAll('[data-form-field]')) {
                    const old_prefix = field.dataset.idx;
                    const new_prefix = `${this.#wrapper.dataset.field}[rows][${row_idx}][columns][${col_idx}][fields][${field_idx}]`;
                    changeFieldPrefixes(field, old_prefix, new_prefix);
                    field.setAttribute('data-idx', new_prefix);

                    field_idx++;
                }

                col_idx++;
            }

            row_idx++;
        }
    }

    #bindAddRow() {
        this.#addRow.onclick = (e) => {
            e.preventDefault();

            const newRow = this.#getPlaceholderDom('row');
            this.#rowsWrap.appendChild(newRow);
            this.#initSortableRow(newRow);
            this.#setRowColumnAndFieldIndexes();
        };
    }
    #bindAddField() {
        this.#addField.onclick = (e) => {
            e.preventDefault();

            const firstCol = this.#rowsWrap.querySelector('[data-row-column]');
            const newField = this.#getPlaceholderDom('field');
            firstCol.querySelector('[data-column-fields]').appendChild(newField);
            this.#bindFieldActions(newField);
        };
    }

    #getPlaceholderDom(placeholder_type) {
        const html = this.#placeholders.querySelector('[data-placeholder="' + placeholder_type + '"]').innerHTML;
        return new DOMParser().parseFromString(html, 'text/html').body.childNodes[0];
    }
}

export function bindFormStructureField(fieldWrap) {
    new FormStructure(fieldWrap);
}