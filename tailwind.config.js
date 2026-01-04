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
                    primary: '#EF7722',   // Oranye Utama
                    secondary: '#FAA533', // Oranye Muda
                    accent: '#0BA6DF',    // Biru Langit
                    light: '#EBEBEB',     // Abu-abu Terang
                    dark: '#1F2937',      // Abu-abu Gelap (Text)
                }
            }
        },
    },

    plugins: [forms],
};
