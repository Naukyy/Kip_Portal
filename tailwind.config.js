import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#0d59f2', // warna utama KIP
                    700: '#0b49cc',
                    800: '#0938a0',
                    900: '#072d7e',
                    950: '#051e52',
                },
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            },
        },
    },
    plugins: [forms],
    // Pastikan semua safe-list warna dinamis Tailwind tidak di-purge
    safelist: [
        { pattern: /bg-(green|yellow|red)-(100|900)/ },
        { pattern: /border-(green|yellow|red)-400/ },
        { pattern: /text-(green|yellow|red)-(700|300)/ },
    ],
};