import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'media',

    theme: {
        extend: {
            colors: {
                /* -- Scholar Tech: Material 3 Palette -- */
                'primary': '#004ac6',
                'primary-container': '#2563eb',
                'on-primary': '#ffffff',
                'on-primary-container': '#eeefff',
                'on-primary-fixed': '#00174b',
                'on-primary-fixed-variant': '#003ea8',
                'primary-fixed': '#dbe1ff',
                'primary-fixed-dim': '#b4c5ff',
                'inverse-primary': '#b4c5ff',

                'secondary': '#006c49',
                'secondary-container': '#6cf8bb',
                'on-secondary': '#ffffff',
                'on-secondary-container': '#00714d',
                'on-secondary-fixed': '#002113',
                'on-secondary-fixed-variant': '#005236',
                'secondary-fixed': '#6ffbbe',
                'secondary-fixed-dim': '#4edea3',

                'tertiary': '#3e3fcc',
                'tertiary-container': '#585be6',
                'on-tertiary': '#ffffff',
                'on-tertiary-container': '#f1eeff',
                'on-tertiary-fixed': '#07006c',
                'on-tertiary-fixed-variant': '#2f2ebe',
                'tertiary-fixed': '#e1e0ff',
                'tertiary-fixed-dim': '#c0c1ff',

                'error': '#ba1a1a',
                'error-container': '#ffdad6',
                'on-error': '#ffffff',
                'on-error-container': '#93000a',

                'surface': '#faf8ff',
                'surface-dim': '#d9d9e5',
                'surface-bright': '#faf8ff',
                'surface-tint': '#0053db',
                'surface-variant': '#e1e2ed',
                'surface-container': '#ededf9',
                'surface-container-low': '#f3f3fe',
                'surface-container-high': '#e7e7f3',
                'surface-container-highest': '#e1e2ed',
                'surface-container-lowest': '#ffffff',

                'on-surface': '#191b23',
                'on-surface-variant': '#434655',
                'on-background': '#191b23',
                'background': '#faf8ff',

                'outline': '#737686',
                'outline-variant': '#c3c6d7',

                'inverse-surface': '#2e3039',
                'inverse-on-surface': '#f0f0fb',

                'scholar-dark': {
                    'surface': '#111318',
                    'surface-container': '#1d1f27',
                    'surface-container-low': '#191b23',
                    'surface-container-high': '#282a32',
                    'surface-container-highest': '#33353d',
                    'surface-container-lowest': '#0d0e14',
                    'on-surface': '#e3e2ee',
                    'on-surface-variant': '#c3c6d7',
                    'outline': '#8d8f9e',
                    'outline-variant': '#434655',
                },
            },
            fontFamily: {
                headline: ['Manrope', ...defaultTheme.fontFamily.sans],
                body: ['Inter', ...defaultTheme.fontFamily.sans],
                label: ['Inter', ...defaultTheme.fontFamily.sans],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                'DEFAULT': '0.25rem',
                'lg': '0.5rem',
                'xl': '0.75rem',
                '2xl': '1rem',
                '3xl': '1.5rem',
                'full': '9999px',
            },
            boxShadow: {
                'ambient': '0 12px 32px rgba(25, 27, 35, 0.06)',
                'ambient-lg': '0 16px 48px rgba(25, 27, 35, 0.1)',
            },
        },
    },

    safelist: [
        'lg:hidden',
        'lg:flex',
        'lg:items-center',
        'md:hidden',
        'sm:hidden',
    ],

    plugins: [forms],
};
