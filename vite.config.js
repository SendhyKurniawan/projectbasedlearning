import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fg from 'fast-glob';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/design-system.css',
                ...fg.sync('resources/css/pages/**/*.css'),
                'resources/js/app.js',
                'resources/js/code-editor.js',
                'resources/js/markdown-editor.js',
                'resources/js/markdown-renderer.js',
                'resources/js/conference-jitsi.js',
                'resources/js/charts.js'
            ],
            refresh: true,
        }),
    ],
});
