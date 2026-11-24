/* Vite static assets */
import.meta.glob([
    '../images/**',
    '../../../node_modules/tinymce/**',
    '../../../node_modules/cropperjs/dist/**',
]);

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

window.addEventListener('load', () => {
    const langForm = document.querySelector('[data-language-change-form]');
    if (langForm) {
        const dropdown = langForm.querySelector('[name="language"]');
        dropdown.onchange = () => {
            langForm.submit();
        }

        const translateBtns = document.querySelectorAll('[data-translate-toggle]');
        if (translateBtns.length) {
            translateBtns.forEach(translateToggle => {
                translateToggle.onclick = (e) => {
                    e.preventDefault();
                    translateToggle.closest('[data-translate-wrap]').classList.toggle('open');
                }
            });
        }
    }

    import('./components/generic/cms-fields')
        .then(({ bindCMSFields }) => {
            bindCMSFields();
        });

    const deleteChecks = document.querySelectorAll('[data-delete-check]');
    if (deleteChecks.length) {
        import('./components/generic/cms-list')
            .then(({ bindDelete }) => {
                bindDelete(deleteChecks);
            })
    }

    const previewButton = document.querySelector('[data-preview-btn]');
    if (previewButton) {
        import('./components/generic/cms-form')
            .then(({ bindPreviewButton }) => {
                bindPreviewButton(previewButton);
            });
    }

    const previewPane = document.querySelector('[data-preview-pane]');
    if (previewPane) {
        import('./components/generic/cms-form')
            .then(({ bindVisualEditor }) => {
                bindVisualEditor();
            });
    }

    import('./components/generic/cloudflare')
        .then(({ bindCloudflareActions }) => {
            bindCloudflareActions();
        });

    const modalToggles = document.querySelectorAll('[data-modal-toggle]');
    const modals = document.querySelectorAll('[data-modal]');
    if (modalToggles.length || modals.length) {
        import('./components/generic/modals')
            .then(({ bindModalToggles, bindInnerModalEvents }) => {
                if (modalToggles.length) {
                    bindModalToggles(modalToggles);
                }
                if (modals.length) {
                    bindInnerModalEvents(modals);
                }
            });
    }

    const flashes = document.querySelectorAll('[data-flash-msg]');
    if (flashes.length) {
        import('./components/generic/flash')
            .then(({ timedHideFlash }) => {
                timedHideFlash(flashes);
            });
    }

    if (typeof FLASH_BROADCAST != 'undefined') {
        const bc = new BroadcastChannel(FLASH_BROADCAST);
        bc.postMessage('flash success');
    }
});