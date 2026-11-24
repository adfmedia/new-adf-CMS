import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

export function bindCarousels(carousels) {
    carousels.forEach(carousel => {
        new Swiper(carousel, getSwiperConfig(carousel, Navigation, Pagination, Autoplay));
    });

    if (typeof (CAROUSEL_CSS) != 'undefined') {
        const css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = CAROUSEL_CSS;
        css.type = 'text/css';
        document.querySelector('body').appendChild(css);
    }
}

function getSwiperConfig(carousel, Navigation, Pagination, Autoplay) {
    const swiper_config_default = {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        autoplay: false,
        navigation: {
            nextEl: carousel.querySelector('.swiper-button-next'),
            prevEl: carousel.querySelector('.swiper-button-prev'),
        },
        pagination: {
            el: carousel.querySelector('.swiper-pagination'),
            clickable: true
        },
        slidesPerView: 1,
        slidesPerGroup: 1,
        spaceBetween: 0,
        breakpoints: {
            640: {
                slidesPerView: 3,
                slidesPerGroup: 3,
            },
            1024: {
                slidesPerView: 4,
                slidesPerGroup: 4
            }
        },
        on: {
            afterInit: function () {
                const lightboxes = document.querySelectorAll('.swiper-slide-duplicate [data-lightbox]');
                if (lightboxes.length) {
                    import('glightbox')
                        .then(({ default: GLightbox }) => {
                            lightboxes.forEach(lightbox => {
                                new GLightbox({
                                    selector: '[data-lightbox][data-group="' + lightbox.getAttribute('data-group') + '"]'
                                });
                            });
                        });
                }
            }
        }
    };

    switch (carousel.dataset.config) {
        case 'testimonials':
            // Remove responsive breakpoints - 1 slide at a time
            return Object.assign({}, swiper_config_default, {
                breakpoints: {}
            });
        default:
            return swiper_config_default;
    }
}