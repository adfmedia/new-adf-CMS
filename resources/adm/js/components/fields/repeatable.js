import { bindCMSFields, changeFieldPrefixes } from '../generic/cms-fields';
import Sortable from 'sortablejs';

export function bindRepeatableField(repeatableField) {
    const field_name = repeatableField.dataset.repeatableField;
    const rowWrap = repeatableField.querySelector('[data-row-wrap]');
    const addBtn = repeatableField.querySelector('[data-add-row]');

    addBtn.onclick = (e) => {
        e.preventDefault();
        addNewRow(rowWrap, rowWrap.closest('[data-repeatable-field]').dataset.repeatableField);
    };
    bindRemoveButtons(rowWrap, field_name);

    const repeatableSortable = new Sortable(rowWrap, {
        animation: 150,
        handle: '[data-handle]',
        onUpdate: (evt) => {
            reindexRows(rowWrap, rowWrap.closest('[data-repeatable-field]').dataset.repeatableField, evt.item);
        }
    });
}

function getFirstRow(rowWrap) {
    return rowWrap.querySelector('[data-row]');
}

function getRowCount(rowWrap) {
    return rowWrap.querySelectorAll('[data-row]').length;
}

function getNextRowIndex(rowWrap) {
    let idx = 0;
    for (const row of rowWrap.querySelectorAll('[data-row]')) {
        const rowIdx = parseInt(row.dataset.idx);
        if (rowIdx > idx) {
            idx = rowIdx;
        }
    }

    return idx + 1;
}

function addNewRow(rowWrap, field_name) {
    const firstRow = getFirstRow(rowWrap);
    const firstRowIdx = firstRow.dataset.idx;

    if (typeof tinymce != 'undefined') {
        tinymce.triggerSave();
    }
    
    const newRowIdx = getNextRowIndex(rowWrap);
    let first_row_html = firstRow.outerHTML;
    let newRow = new DOMParser().parseFromString(first_row_html, 'text/html').body.childNodes[0];

    for (const mceEditor of newRow.querySelectorAll('.tox-tinymce')) {
        mceEditor.remove();
    }

    for (const new_wysiwyg of newRow.querySelectorAll('[data-wysiwyg]')) {
        new_wysiwyg.setAttribute('id', Date.now() + (Math.random() * 100));
        new_wysiwyg.style.display = null;
    }

    changeFieldPrefixes(newRow, `${field_name}[${firstRowIdx}]`, `${field_name}[${newRowIdx}]`, true);
    newRow.setAttribute('data-idx', newRowIdx);

    rowWrap.appendChild(newRow);

    // Bind any JS-interacted fields
    bindCMSFields(newRow);

    newRow.addEventListener('image-fields-bound', (e) => {
        // Remove any selected images
        for (const removeImageLink of newRow.querySelectorAll('[data-image-tile] [data-remove-image]')) {
            removeImageLink.dispatchEvent(new Event('click'));
        }
    });

    bindRemoveButtons(rowWrap, field_name);
}

function bindRemoveButtons(rowWrap, field_name) {
    for (const removeBtn of rowWrap.querySelectorAll('[data-remove-row]')) {
        removeBtn.onclick = (e) => {
            e.preventDefault();

            if (getRowCount(rowWrap) <= 1) { return false; }

            removeBtn.closest('[data-row]').remove();

            reindexRows(rowWrap, field_name);
        }
    }
}

function reindexRows(rowWrap, field_name, moved_row) {
    if (typeof tinymce != 'undefined') {
        tinymce.triggerSave();
    }
    if (moved_row) {
        for (const moved_wysiwyg of moved_row.querySelectorAll('[data-wysiwyg]')) {
            const editor = tinymce.get(moved_wysiwyg.getAttribute('id'));
            if (editor) {
                tinymce.remove(editor);
            }
        }
    }

    let row_idx = 0;
    for (const row of rowWrap.querySelectorAll('[data-row]')) {
        changeFieldPrefixes(row, `${field_name}[${row.dataset.idx}]`, `${field_name}[${row_idx}]`);
        row.setAttribute('data-idx', row_idx);

        row_idx++;
    }

    if (typeof (window.loadWysiwygs) != 'undefined') {
        window.loadWysiwygs();
    }
}