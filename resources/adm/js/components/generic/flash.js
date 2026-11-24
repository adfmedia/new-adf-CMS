export function timedHideFlash(flashes, time, refresh) {
    if (typeof time == 'undefined') {
        time = 3000;
    }
    if (typeof refresh == 'undefined') {
        refresh = false;
    }

    if (flashes.length) {
        setTimeout(() => {
            for (const flash of flashes) {
                flash.remove();
            }
            if (refresh) {
                window.location.reload();
            }
        }, time);
    }
}

export function addFlash(type, message, refresh) {
    const breadcrumbBar = document.querySelector('[data-breadcrumb-bar]');

    const flash = `<div data-flash-msg class="flash-msg ${type}">${message}</div>`;
    const flash_msg = new DOMParser().parseFromString(flash, 'text/html').body.childNodes[0];
    breadcrumbBar.appendChild(flash_msg);

    const flashes = breadcrumbBar.querySelectorAll('[data-flash-msg]');
    timedHideFlash(flashes, 3000, refresh);
}