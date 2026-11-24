import { addFlash } from './flash';

export function bindDelete(checkboxes) {
    const deleteForm = document.querySelector('[data-delete-check-form]');
    const deleteBtn = deleteForm.querySelector('[data-submit-btn]');
    const deleteModal = deleteForm.querySelector('[data-modal="delete-confirm"]');
    const checkAll = document.querySelector('[data-delete-check-all]');

    const anyChecked = () => {
        for (const checkbox of checkboxes) {
            if (checkbox.checked) {
                return true;
            }
        }

        return false;
    };
    const showHideForm = () => {
        if (anyChecked()) {
            deleteForm.classList.remove('hidden');
            deleteForm.classList.add('flex');
        } else {
            deleteForm.classList.add('hidden');
            deleteForm.classList.remove('flex');
        }
    };

    checkAll.onchange = () => {
        for (const checkbox of checkboxes) {
            checkbox.checked = checkAll.checked;
        }
        showHideForm();
    }
    for (const checkbox of checkboxes) {
        checkbox.onchange = () => {
            showHideForm();
        }
    }

    deleteBtn.onclick = (e) => {
        e.preventDefault();
        if (!anyChecked()) { return false; }

        deleteModal.classList.add('open');
    };
    deleteModal.querySelector('[data-confirm-btn]').onclick = (e) => {
        e.preventDefault();
        if (!anyChecked()) { 
            deleteModal.classList.remove('open');
            return false; 
        }
        
        let postBody = new FormData(deleteForm);
        for (const checkbox of checkboxes) {
            if (checkbox.checked) {
                postBody.append(checkbox.getAttribute('name'), checkbox.value);
            }
        }

        fetch(deleteForm.getAttribute('action'), {
            method: deleteForm.getAttribute('method'),
            body: postBody,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(resp => resp.json())
        .then(json => {
            deleteModal.classList.remove('open');
            if (json.success) {
                window.location.reload();
            } else {
                addFlash('error', json.error, true);
            }
        });
    };
}