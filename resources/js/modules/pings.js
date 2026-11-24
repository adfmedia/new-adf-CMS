import { jsonPostRequest, fetchPostRequest } from "./xhr";

export function runCommonPings() {
    window.addEventListener('token-regenerated', () => {
        tracking_data();
        cms_url();
    }, true);

    renew_token();
}

function renew_token() {
    if (typeof CF_STATUS != 'undefined' && CF_STATUS) {
        const renewBody = new FormData();
        renewBody.append('foo', 'bar');

        jsonPostRequest('/ajax/ping/token', renewBody, false)
        .then(json => {
            if (json.token) {
                document.querySelector('[name="csrf-token"]').setAttribute('content', json.token);
                for (const tokenField of document.querySelectorAll('[name="_token"]')) {
                    tokenField.value = json.token;
                }
            }
            window.dispatchEvent(new Event('token-regenerated'));
        })
        .catch(() => {
            window.dispatchEvent(new Event('token-regenerated'));
        });
    } else {
        window.dispatchEvent(new Event('token-regenerated'));
    }
}

async function tracking_data() {
    const tdBody = new FormData();
    tdBody.append('page_url', window.location.href);
    tdBody.append('referrer', document.referrer);

    fetchPostRequest('/ajax/ping/form_tracking', tdBody);
}

async function cms_url() {
    if (typeof URLID == 'undefined') { return false; }

    const urlBody = new FormData();
    urlBody.append('url', URLID);

    jsonPostRequest('/ajax/ping/cms_url', urlBody)
    .then(json => {
        if (json.url) {
            const link_html = `
                <a 
                    href="${json.url}" target="_blank" 
                    class="btn btn--black fixed right-0 bottom-0 mb-2 mr-2"
                >Edit this page</a>
            `
            const link_dom = new DOMParser().parseFromString(link_html, 'text/html').body.childNodes[0];
            document.querySelector('body').appendChild(link_dom);
        }
    })
    .catch(err => {});
}