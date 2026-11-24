export function getCookie (cname) {
    const name = cname + "=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};
export function setCookie (cname, value, days) {
    if (typeof (days) == 'undefined') {
        days = 365;
    }
    const expiry_ms = (1000 * 60 * 60 * 24 * parseInt(days));
    const now = new Date().getTime();
    const expiry = new Date(now + expiry_ms);
    const secure = typeof (COOKIE_SECURE) != 'undefined' && COOKIE_SECURE ? 'secure' : '';

    document.cookie = cname + '=; expires=0; path=/;' + secure;
    document.cookie = cname + '=' + value + '; expires=' + expiry.toUTCString() + '; path=/;' + secure;
}