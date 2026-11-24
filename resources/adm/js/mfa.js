window.addEventListener('load', () => {
    const mfaForm = document.querySelector('[data-mfa-form]');
    mfaForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const err = mfaForm.querySelector('[data-error]');
        const success = mfaForm.querySelector('[data-success]');
        err.classList.add('hidden');
        success.classList.add('hidden');

        fetch(mfaForm.dataset.mfaForm, {
            method: 'POST',
            body: new FormData(mfaForm)
        })
        .then(resp => resp.json())
        .then(json => {
            if (json.success) {
                success.classList.remove('hidden');
            }
            if (json.error) {
                err.innerText = json.error;
                err.classList.remove('hidden');
            }
            if (json.redirect) {
                window.location.href = json.redirect;
            }
        })
        .catch((error) => {
            console.error(error);
            err.innerText = 'Sorry, something went wrong verifying your code. Please try again later.';
            err.classList.remove('hidden');
        });
    });
}, true);