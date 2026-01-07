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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // TAMBAHAN: Palet Warna Bubur
            colors: {
                bubur: {
                    primary: '#EF7722',
                    secondary: '#FAA533',
                    accent: '#0BA6DF',
                    light: '#EBEBEB',
                    dark: '#1F2937',
                }
            }
        },
    },

    plugins: [forms],
};
