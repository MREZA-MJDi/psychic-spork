import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/store-structure.css',
                'resources/css/store-polish.css',
                'resources/css/store-responsive.css',
                'resources/css/store-customer-uiux.css',
                'resources/css/responsive-shell.css',
                'resources/css/home.css',
                'resources/css/wholesale.css',
                'resources/css/editorial-hero.css',
                'resources/css/auth.css',
                'resources/js/app.js',
                'resources/js/store-cart.js',
                'resources/js/store-search.js',
                'resources/js/product-show.js',
                'resources/js/editorial-hero.js',
                'resources/js/home-product-carousel.js',
                'resources/css/admin.css',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
