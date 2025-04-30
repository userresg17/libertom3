# /public/build/ Directory Structure

This directory contains assets compiled by Vite. In a Laravel project with Vite, the following structure would be generated:

```
public/build/
├── assets/
│   ├── app-[hash].js              # Main JavaScript bundle
│   ├── app-[hash].css             # Main CSS bundle
│   ├── dashboard-[hash].js        # Dashboard-specific JavaScript bundle
│   ├── dashboard-[hash].css       # Dashboard-specific CSS bundle  
│   ├── admin-[hash].js            # Admin panel JavaScript bundle
│   ├── admin-[hash].css           # Admin panel CSS bundle
│   └── images/                    # Optimized images
│       ├── logo-[hash].png
│       ├── logo-dark-[hash].png
│       ├── favicon-[hash].png
│       └── icons/
│           ├── dashboard-[hash].svg
│           ├── wallet-[hash].svg
│           ├── investment-[hash].svg
│           ├── goldstay-[hash].svg
│           ├── giftcard-[hash].svg
│           ├── send-[hash].svg
│           ├── settings-[hash].svg
│           └── logout-[hash].svg
├── manifest.json                  # Asset manifest for Vite
└── _vite_manifest.json            # Vite manifest file

```

## Notes

- The `[hash]` suffix is a unique identifier added by Vite for cache busting
- The `manifest.json` file maps original asset paths to the compiled versions with hashes
- In production, all CSS and JavaScript will be minified and optimized
- Images are typically optimized for web usage
- The structure may slightly change depending on the specific Vite configuration

## Manifest Example

The `manifest.json` file would look something like this:

```json
{
  "resources/css/app.css": {
    "file": "assets/app-a1b2c3d4.css",
    "isEntry": true,
    "src": "resources/css/app.css"
  },
  "resources/js/app.js": {
    "file": "assets/app-e5f6g7h8.js",
    "isEntry": true,
    "src": "resources/js/app.js"
  },
  "resources/js/dashboard.js": {
    "file": "assets/dashboard-i9j0k1l2.js",
    "isEntry": true,
    "src": "resources/js/dashboard.js"
  },
  "resources/js/admin.js": {
    "file": "assets/admin-m3n4o5p6.js",
    "isEntry": true,
    "src": "resources/js/admin.js"
  }
}
```

This directory is automatically generated during the build process by running:

```
npm run build
```

or

```
yarn build
```

depending on your package manager.