/* NPM version 22 */
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { local } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                /* Front-end resources */
                'resources/css/app.css',
                'resources/css/carousel.css',
                'resources/css/lightbox.css',
                'resources/js/app.js',
                'resources/js/cookie-banner.js',

                /* Admin resources */
                'resources/adm/css/style.css',
                'resources/adm/js/main.js',
                'resources/adm/js/file-manager.js',
                'resources/adm/js/menu-builder.js',
                'resources/adm/js/wysiwygs.js',
                'resources/adm/js/mfa.js',
            ],
            assets: [
                'resources/images/**',
                'resources/adm/images/**',
            ],
            fonts: [
                local('Figtree', {
                    alias: 'figtree',
                    variants: [
                        {
                            src: [
                                'resources/fonts/Figtree-VariableFont_wght.woff2',
                                'resources/fonts/Figtree-VariableFont_wght.ttf'
                            ],
                            weight: '100 1000',
                            display: 'swap',
                        },
                        {
                            src: [
                                'resources/fonts/Figtree-Italic-VariableFont_wght.woff2',
                                'resources/fonts/Figtree-Italic-VariableFont_wght.ttf'
                            ],
                            weight: '100 1000',
                            style: 'italic',
                            display: 'swap',
                        }
                    ],
                    optimizedFallbacks: false
                }),
                local('Inter', {
                    alias: 'inter',
                    variants: [
                        {
                            src: 'resources/adm/fonts/inter-latin-vf.woff2',
                            weight: '100 900',
                            display: 'swap',
                        }
                    ],
                    optimizedFallbacks: false
                })
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        }
    },
    build: {
        chunkSizeWarningLimit: 2000
    }
});
