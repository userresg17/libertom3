# Libertom Financial Platform - Public Directory

This document provides an overview of the public directory structure and files created for the Libertom Financial Platform.

## Core Files

| File | Description |
|------|-------------|
| **index.php** | Entry point for the Laravel application that handles all HTTP requests |
| **robots.txt** | Directives for search engine crawlers, protecting sensitive routes |
| **favicon.ico** | Browser tab icon representing the Libertom brand |

## Asset Files

| File | Description |
|------|-------------|
| **css/app.css** | Main compiled CSS file with Tailwind CSS framework and custom styles |
| **js/app.js** | Main compiled JavaScript file with all frontend functionality |

## Directory Structures

| Directory | Description |
|-----------|-------------|
| **build/** | Contains Vite-compiled assets with proper cache-busting through file hashing |
| **uploads/** | Organized structure for user-uploaded files, particularly for KYC verification |

## Security Considerations

The public directory has been structured with the following security considerations:

1. Sensitive routes are disallowed in robots.txt
2. User uploaded files have a clear organization with appropriate .gitignore rules
3. Compiled assets include cache-busting mechanisms
4. The index.php file properly handles application bootstrapping and request handling

## Frontend Architecture

The frontend architecture implements:

1. **AlpineJS** for lightweight JavaScript functionality
2. **TailwindCSS** for responsive design
3. **Chart.js** and **ApexCharts** for investment dashboards
4. **Web3/Ethers** integration for GoldStay token functionality

## Responsive Design

All elements are designed to be responsive across:
- Mobile devices
- Tablets
- Desktop computers

The responsive design is primarily handled through TailwindCSS utility classes.

## Next Steps

After setting up the public directory, the following steps should be completed:

1. Set up proper Laravel routes in `routes/web.php`
2. Create controllers for each major feature
3. Implement database migrations and models
4. Create Blade templates for the views
5. Implement API integrations with Banco Cora, Stripe, and Polygon network

## Development Notes

- For local development, use `php artisan serve`
- For frontend asset compilation during development, use `npm run dev`
- For production builds, use `npm run build`