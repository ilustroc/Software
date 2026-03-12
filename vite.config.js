import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/auth.css',
                'resources/css/dashboard.css',
                'resources/css/panel.css',
                'resources/css/gestiones.css',
                'resources/css/reportes.css',
                'resources/css/pagos.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});