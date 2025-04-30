const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 | Note: This file is provided as a fallback in case you need to switch from
 | Vite to Laravel Mix. The primary build system for this project is Vite.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
        require('autoprefixer'),
    ])
    .sourceMaps();

// Copy any static assets
mix.copy('resources/images', 'public/images');

// Set production mode based on environment
if (mix.inProduction()) {
    mix.version(); // Add versioning in production for cache busting
}

// Webpack specific configuration
mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
        }
    },
    output: {
        chunkFilename: 'js/chunks/[name].js',
    },
});