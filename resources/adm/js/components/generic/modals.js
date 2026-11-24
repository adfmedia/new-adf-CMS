export function bindModalToggles(modalToggles) {

}

export function bindInnerModalEvents(modals) {
    for (const modal of modals) {
        for (const closeBtn of modal.querySelectorAll('[data-modal-close]')) {
            closeBtn.onclick = (e) => {
                e.preventDefault();

                modal.classList.remove('open');
            }
        }
    }
}