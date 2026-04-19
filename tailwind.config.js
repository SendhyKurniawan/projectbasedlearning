import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            colors: {
                /* -- Scholar Tech: Material 3 Palette (brand — static) -- */
                'primary': '#004ac6',
                'primary-container': '#2563eb',
                'on-primary': '#ffffff',
                'on-primary-container': '#eeefff',
                'on-primary-fixed': '#00174b',
                'on-primary-fixed-variant': '#003ea8',
                'primary-fixed': '#dbe1ff',
                'primary-fixed-dim': '#b4c5ff',

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

                'warning': '#eab308',
                'warning-light': '#fef9c3',
                'on-warning': '#422006',
                'primary-hover': '#003ea8',

                /* -- Surface / semantic tokens — flip with .dark class -- */
                'surface': 'rgb(var(--surface-rgb) / <alpha-value>)',
                'surface-dim': 'rgb(var(--surface-dim-rgb) / <alpha-value>)',
                'surface-bright': 'rgb(var(--surface-bright-rgb) / <alpha-value>)',
                'surface-tint': 'rgb(var(--surface-tint-rgb) / <alpha-value>)',
                'surface-variant': 'rgb(var(--surface-variant-rgb) / <alpha-value>)',
                'surface-container': 'rgb(var(--surface-container-rgb) / <alpha-value>)',
                'surface-container-low': 'rgb(var(--surface-container-low-rgb) / <alpha-value>)',
                'surface-container-high': 'rgb(var(--surface-container-high-rgb) / <alpha-value>)',
                'surface-container-highest': 'rgb(var(--surface-container-highest-rgb) / <alpha-value>)',
                'surface-container-lowest': 'rgb(var(--surface-container-lowest-rgb) / <alpha-value>)',
                'on-surface': 'rgb(var(--on-surface-rgb) / <alpha-value>)',
                'on-surface-variant': 'rgb(var(--on-surface-variant-rgb) / <alpha-value>)',
                'on-background': 'rgb(var(--on-background-rgb) / <alpha-value>)',
                'background': 'rgb(var(--background-rgb) / <alpha-value>)',
                'outline': 'rgb(var(--outline-rgb) / <alpha-value>)',
                'outline-variant': 'rgb(var(--outline-variant-rgb) / <alpha-value>)',
                'inverse-surface': 'rgb(var(--inverse-surface-rgb) / <alpha-value>)',
                'inverse-on-surface': 'rgb(var(--inverse-on-surface-rgb) / <alpha-value>)',
                'inverse-primary': 'rgb(var(--inverse-primary-rgb) / <alpha-value>)',
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
