import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/store.css',
                'resources/css/home.css',
                'resources/css/hero.css',
                'resources/css/wholesale.css',
                'resources/css/auth.css',
                'resources/js/app.js',
                'resources/js/store-cart.js',
                'resources/js/store.js',
                'resources/js/store-search.js',
                'resources/js/product-show.js',
                'resources/js/editorial-hero.js',
                'resources/js/home-product-carousel.js',
                'resources/css/admin.css',
                'resources/css/admin-responsive.css',
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
