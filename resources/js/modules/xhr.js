export async function jsonPostRequest(url, data, append_token) {
    const resp = await fetchPostRequest(url, data, append_token);
    return await resp.json();
}

export async function jsonGetRequest(url) {
    const resp = await fetchGetRequest(url);
    return await resp.json();
}

export async function textPostRequest(url, data, append_token) {
    const resp = await fetchPostRequest(url, data, append_token);
    return await resp.text();
}

export async function textGetRequest(url) {
    const resp = await fetchGetRequest(url);
    return await resp.text();
}

export async function fetchPostRequest(url, data, append_token) {
    if (typeof append_token == 'undefined') {
        append_token = true;
    }

    if (append_token) {
        data = appendCsrf(data);
    }

    return await fetch(
        url,
        {
            method: 'POST',
            body: data,
            headers: getFetchHeaders()
        }
    );
}

export async function fetchGetRequest(url) {
    return await fetch(
        url,
        {
            headers: getFetchHeaders()
        }
    )
}

function appendCsrf(data) {
    data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
    return data;
}

function getFetchHeaders() {
    return {
        'X-Requested-With': 'XMLHttpRequest'
    };
}