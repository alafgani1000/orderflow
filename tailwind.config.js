import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                '2xs': ['0.65rem', { lineHeight: '1rem' }],
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.25rem',
            },
        },
    },

    plugins: [
        forms({
            strategy: 'class',
        }),
    ],
};
