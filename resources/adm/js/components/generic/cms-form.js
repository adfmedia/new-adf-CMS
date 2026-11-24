export function bindPreviewButton(btn) {
    const cmsForm = document.querySelector('[data-cms-form]');
    if (!cmsForm) { 
        return; 
    }

    btn.onclick = (e) => {
        e.preventDefault();

        const origAction = cmsForm.getAttribute('action');
        cmsForm.setAttribute('action', btn.getAttribute('href'));
        cmsForm.setAttribute('target', 'blank');
        cmsForm.requestSubmit();
        cmsForm.setAttribute('action', origAction);
        cmsForm.removeAttribute('target');
    };
}

export function bindVisualEditor() {
    const cmsForm = document.querySelector('[data-cms-form]');
    const previewPane = document.querySelector('[data-preview-pane]');
    const loader = document.querySelector('[data-preview-loader]');
    if (!cmsForm || !previewPane) {
        return;
    }

    cmsForm.querySelectorAll('[required]').forEach((reqInpt) => {
        reqInpt.removeAttribute('required');
    });

    let loading = false;
    let previewScroll = 0;
    previewPane.addEventListener('load', () => {
        loading = false;
        if (loader) {
            loader.classList.add('hidden');
        }
        previewPane.contentWindow.scrollTo(0, previewScroll);
        previewPane.contentWindow.addEventListener('scroll', (e) => {
            previewScroll = previewPane.contentWindow.scrollY;
        }, true);

        const preventReturn = (e) => {
            e.preventDefault();
            return false;
        }
        previewPane.contentDocument.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', preventReturn);
        });
        previewPane.contentDocument.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', preventReturn);
        });
    }, true);

    const refreshPreview = () => {
        if (loading) { return false; }
        loading = true;

        if (loader) {
            loader.classList.remove('hidden');
        }

        const origAction = cmsForm.getAttribute('action');
        cmsForm.setAttribute('action', previewPane.dataset.src);
        cmsForm.setAttribute('target', previewPane.getAttribute('name'));
        cmsForm.requestSubmit();
        cmsForm.setAttribute('action', origAction);
        cmsForm.removeAttribute('target');
    };

    refreshPreview();

    const bindFormInputs = () => {
        cmsForm.querySelectorAll('input,select,textarea').forEach((formEl) => {
            formEl.addEventListener('change', refreshPreview);
        });
        cmsForm.querySelectorAll('button.toggle').forEach((toggle) => {
            toggle.addEventListener('click', refreshPreview);
        });
    }
    bindFormInputs();
    document.addEventListener('cms-fields-bound', bindFormInputs);
    document.addEventListener('page-builder-update', refreshPreview);

    const previewSizer = document.querySelector('[data-preview-sizer]');
    if (previewSizer) {
        const resizers = document.querySelectorAll('[data-preview-resizer]');
        resizers.forEach((resizer) => {
            resizer.addEventListener('click', (e) => {
                e.preventDefault();
                resizers.forEach((other) => {
                    previewSizer.classList.remove(other.dataset.width);
                    other.classList.remove('bg-primary-medium');
                    other.classList.remove('text-white');
                });
                previewSizer.classList.add(resizer.dataset.width);
                resizer.classList.add('bg-primary-medium');
                resizer.classList.add('text-white');
            });
        });
    }
}