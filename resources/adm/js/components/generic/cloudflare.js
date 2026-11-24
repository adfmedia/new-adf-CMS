export function bindCloudflareActions() {
    const cfBtns = document.querySelectorAll('[data-cf-btn]');
    if (!cfBtns.length) { return; }

    for (const cfBtn of cfBtns) {
        cfBtn.onclick = (e) => {
            e.preventDefault();

            switch (cfBtn.dataset.cfBtn) {
                case 'purge':
                    purgeCfCache(cfBtn);
                    break;
                case 'dev-mode':
                    toggleCfDevMode(cfBtn);
                    break;
            }
        };
    }
}

function purgeCfCache(btn) {
    if (btn.dataset.purging == "true") { return; }

    btn.dataset.purging = true;
    const origText = btn.innerText;
    btn.innerText = 'Purging cache...';

    const data = new FormData();
    data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));

    fetch(
        '/adm/ajax/tools/cf_purge',
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
        if (json.success) {
            btn.innerText = 'Purge succeeded';
            btn.classList.add('text-green-600');
        } else {
            btn.innerText = 'Purge failed';
            btn.classList.add('text-red-600');
        }
        setTimeout(() => {
            btn.innerText = origText;
            btn.classList.remove('text-red-600', 'text-green-600');
        }, 1500);
    })
    .catch(err => {
        btn.innerText = 'Purge failed';
        btn.classList.add('text-red-600');
        setTimeout(() => {
            btn.innerText = origText;
            btn.classList.remove('text-red-600');
        }, 1500);
    })
    .finally(() => {
        btn.dataset.purging = false;
    });
}

function toggleCfDevMode(btn) {
    if (btn.dataset.switching == "true") { return; }

    const data = new FormData();
    data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));

    fetch(
        '/adm/ajax/tools/cf_dev_mode_switch',
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
        btn.classList.remove('on', 'off');
        if (json.success) {
            btn.classList.add(json.status);
        }
    })
    .catch(err => {
    })
    .finally(() => {
        btn.dataset.switching = false;
    });
}