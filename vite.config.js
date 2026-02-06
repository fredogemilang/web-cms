import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { glob } from 'glob';
import path from 'path';

// Discover theme assets dynamically
function discoverThemeAssets() {
    const themeAssets = [];

    try {
        // Find all theme CSS files
        const themeCss = glob.sync('themes/*/assets/css/theme.css');
        themeAssets.push(...themeCss);

        // Find all theme JS files
        const themeJs = glob.sync('themes/*/assets/js/theme.js');
        themeAssets.push(...themeJs);
    } catch (error) {
        console.warn('Theme asset discovery failed:', error.message);
    }

    return themeAssets;
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                ...discoverThemeAssets(),
            ],
            refresh: [
                'resources/views/**',
                'themes/**/views/**',
                'plugins/**/resources/views/**',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    resolve: {
        alias: {
            '@themes': path.resolve(__dirname, 'themes'),
        },
    },
});
