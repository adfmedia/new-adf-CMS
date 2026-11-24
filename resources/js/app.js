if (typeof ALPJS != 'undefined' && ALPJS) {
    import('./modules/alpine');
}

window.addEventListener('load', () => {
    /* Mobile nav */
    const mobNavToggle = document.querySelector('[data-mobile-nav-toggle]');
    const mobNav = document.querySelector('[data-mobile-nav]');
    if (mobNavToggle && mobNav) {
        const mobNavInner = mobNav.querySelector('[data-mobile-nav-inner]');
        if (mobNavInner.hasAttribute('data-sticky-header')) {
            const stickyHeader = document.querySelector(mobNavInner.dataset.stickyHeader);
            if (stickyHeader) {
                mobNavInner.style.paddingTop = stickyHeader.offsetHeight + 'px';
            }
        }

        mobNavToggle.onclick = (e) => {
            e.preventDefault();
            mobNav.classList.toggle('hidden');
            document.querySelector('body').classList.toggle('overflow-hidden');

            const openIcon = mobNavToggle.querySelector('[data-open-icon]');
            const closeIcon = mobNavToggle.querySelector('[data-close-icon]');
            if (openIcon && closeIcon) {
                openIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            }
        };

        mobNav.querySelectorAll('[data-has-sub]').forEach((subLink) => {;
            const toggle = subLink.querySelector('[data-sub-toggle]');
            const icon = subLink.querySelector('[data-sub-icon]');
            const nav = subLink.querySelector('[data-sub-nav]');

            if (toggle && nav) {
                toggle.onclick = (e) => {
                    e.preventDefault();
                    nav.classList.toggle('hidden');

                    if (icon) {
                        icon.classList.toggle('rotate-180');
                    }
                }
            }
        });
    }
    /* END Mobile nav */

    const cmsForms = document.querySelectorAll('[data-cms-form]');
    if (cmsForms.length) {
        import('./modules/form-submissions')
            .then(({ bindCmsForms }) => {
                bindCmsForms(cmsForms);
            });
    }

    const lightboxes = document.querySelectorAll('[data-lightbox]');
    if (lightboxes.length) {
        import('./modules/lightboxes')
            .then(({ bindLightboxes }) => {
                bindLightboxes(lightboxes);
            });
    }

    const carousels = document.querySelectorAll('[data-carousel]');
    if (carousels.length) {
        import('./modules/carousels')
            .then(({ bindCarousels }) => {
                bindCarousels(carousels);
            });
    }

    const accordionWraps = document.querySelectorAll('[data-accordion-wrap]');
    if (accordionWraps.length) {
        import('./modules/accordions')
            .then(({ bindAccordions }) => {
                bindAccordions(accordionWraps);
            })
    }

    const maps = document.querySelectorAll('[data-map]');
    if (maps.length) {
        import('./modules/maps')
            .then(({ bindMaps }) => {
                bindMaps(maps);
            });
    }

    const loadMores = document.querySelectorAll('[data-load-more]');
    if (loadMores.length) {
        import('./modules/load-more')
            .then(({ bindLoadMore }) => {
                for (const loadMore of loadMores) {
                    bindLoadMore(loadMore);
                }
            })
    }

    const dropdownToggles = document.querySelectorAll('[data-dropdown-toggle]');
    if (dropdownToggles.length) {
        import('./modules/dropdown-toggles')
            .then(({ bindDropdownToggles }) => {
                bindDropdownToggles(dropdownToggles);
            });
    }

    import('./modules/pings')
        .then(({ runCommonPings }) => {
            runCommonPings();
        });
}, true);