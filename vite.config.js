import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js', 
                'resources/js/promethee-result.js',
                'resources/css/profile/edit.css',
                'resources/css/dashboard/syle.css',
                'resources/js/chart.js',
                'resources/css/decisions/results-style.css',
                'js/decisions/calculate.js'
            ],
            refresh: true,
        }),
    ],
});
