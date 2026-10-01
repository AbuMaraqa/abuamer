import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Body text for both languages (the family ships Arabic and Latin glyphs).
                bunny('IBM Plex Sans Arabic', {
                    alias: 'body',
                    weights: [300, 400, 500, 600, 700],
                    subsets: ['arabic', 'latin'],
                    preload: [{ weight: 400 }],
                }),
                // Display headings: Latin glyphs come from Cormorant, Arabic glyphs fall back to Noto Kufi.
                bunny('Cormorant Garamond', {
                    alias: 'display-latin',
                    weights: [400, 500, 600],
                    subsets: ['latin'],
                    preload: false,
                }),
                bunny('Noto Kufi Arabic', {
                    alias: 'display-arabic',
                    weights: [300, 400, 500, 600],
                    subsets: ['arabic'],
                    preload: false,
                }),
            ],
        }),
        inertia({
            ssr: false,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**', '**/.claude/**'],
        },
    },
});
