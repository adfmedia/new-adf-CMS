export function bindAccordions(wrappers) {
    wrappers.forEach((wrapper) => {
        wrapper.querySelectorAll('[data-accordion-item]').forEach((item) => {
            const toggle = item.querySelector('[data-item-toggle]');
            if (toggle) {
                toggle.onclick = (e) => {
                    e.preventDefault();
                    item.classList.toggle('open');
                }
            }
        });
    });
}