/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                'libertom': {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1', // Primary brand color
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
                'gold': {
                    50: '#fefce8',
                    100: '#fef9c3',
                    200: '#fef08a',
                    300: '#fde047',
                    400: '#facc15',
                    500: '#eab308', // Gold primary
                    600: '#ca8a04',
                    700: '#a16207',
                    800: '#854d0e',
                    900: '#713f12',
                    950: '#422006',
                },
                'success': {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    500: '#10b981',
                    700: '#047857',
                },
                'warning': {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    500: '#f59e0b',
                    700: '#b45309',
                },
                'danger': {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    500: '#ef4444',
                    700: '#b91c1c',
                },
                'info': {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    500: '#3b82f6',
                    700: '#1d4ed8',
                },
            },
            fontFamily: {
                'sans': ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                'display': ['Montserrat', 'Inter', 'ui-sans-serif', 'system-ui'],
                'mono': ['JetBrains Mono', 'monospace'],
            },
            boxShadow: {
                'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            },
            borderRadius: {
                'xl': '1rem',
                '2xl': '1.5rem',
            },
            animation: {
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'bounce-slow': 'bounce 2s infinite',
            },
            screens: {
                'xs': '475px',
                '3xl': '1920px',
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
            },
            minHeight: {
                'screen-75': '75vh',
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        color: theme('colors.gray.800'),
                        a: {
                            color: theme('colors.libertom.600'),
                            '&:hover': {
                                color: theme('colors.libertom.700'),
                            },
                        },
                        'h1, h2, h3, h4, h5, h6': {
                            color: theme('colors.gray.900'),
                            fontFamily: theme('fontFamily.display').join(', '),
                        },
                    },
                },
            }),
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
    // Safelist essential classes to ensure they're not purged
    safelist: [
        'bg-success-500',
        'bg-warning-500',
        'bg-danger-500',
        'bg-info-500',
        'text-success-500',
        'text-warning-500',
        'text-danger-500',
        'text-info-500',
    ]
};