import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                navy: {
                    800: '#152238',
                    900: '#0A1128',
                },
                gold: {
                    300: '#E0C366',
                    400: '#D4AF37',
                    500: '#B08D22',
                    600: '#8C6F1B',
                }
            }
        },
    },

    plugins: [forms],
};
