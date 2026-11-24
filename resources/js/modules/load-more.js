import { jsonGetRequest } from "./xhr";

export function bindLoadMore(wrapper) {
    let page = 1;
    const url = wrapper.dataset.url;
    const listings = wrapper.querySelector('[data-listings]');
    const btn = wrapper.querySelector('[data-load-more-btn]');

    if (!url || !listings) {
        return;
    }

    const loadResults = () => {
        page++;

        if (btn) {
            const btn_url = btn.getAttribute('href');
            history.pushState({}, '', btn_url);
        }
        
        jsonGetRequest(url + (url.includes('?') ? '&' : '?') + 'page=' + page)
        .then(json => {
            if (json.listings) {
                const new_listings = new DOMParser().parseFromString(json.listings, 'text/html').body.childNodes;
                for (const listing of new_listings) {
                    listings.append(listing);
                }
            }

            if (btn) {
                if (!json.has_more) {
                    btn.remove();
                } else {
                    btn.setAttribute('href', btn_url.replace('page=' + page, 'page=' + (page+1)));
                }
            }
        })
        .catch(() => {
            if (btn) {
                btn.remove();
            }
        });
    }

    if (btn) {
        btn.onclick = (e) => {
            e.preventDefault();
            loadResults();
        }
    }

    const infTarget = wrapper.querySelector('[data-infinite-target]');
    if (wrapper.dataset.infinite == "true" && infTarget && typeof IntersectionObserver != undefined) {
        const offset = infTarget.hasAttribute('data-scroll-offset') ? parseInt(infTarget.dataset.scrollOffset) : 150;
        let observer;
        observer = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) { return; }
                    loadResults();
                });
            }, {
            rootMargin: `0px 0px ${offset}px 0px`,
            threshold: 0
        }
        );
        observer.observe(infTarget);
    }
}