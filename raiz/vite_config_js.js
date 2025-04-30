import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        // Use relative base path
        outDir: 'public/build',
        emptyOutDir: true,
        // Set sourcemap in production to assist debugging
        sourcemap: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split vendor dependencies
                    'vendor': [
                        'alpinejs',
                        'axios',
                        'chart.js',
                        'apexcharts',
                        'ethers',
                        'web3'
                    ],
                },
            },
        },
    },
    optimizeDeps: {
        include: [
            'alpinejs',
            'axios',
        ],
    },
    server: {
        // Allow external access over network
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
    },
});