import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/design-system.css',
                'resources/js/app.js',
                'resources/js/code-editor.js',
                'resources/js/markdown-editor.js',
                'resources/js/conference-room.js',
                'resources/js/conference-meet-enhancements.js'
            ],
            refresh: true,
        }),
    ],
});
