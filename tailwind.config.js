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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Palet GalleryProperty: hijau zamrud tua (utama), emas (aksen), krem (latar)
                primary: {
                    50: '#EEF7F3',
                    100: '#D5EDE3',
                    200: '#ABDAC7',
                    300: '#78C0A5',
                    400: '#45A07F',
                    500: '#238464',
                    600: '#166B51',
                    700: '#115643',
                    800: '#0F4537',
                    900: '#0C392E',
                    950: '#06201A',
                },
                gold: {
                    50: '#FBF7EB',
                    100: '#F5EBCB',
                    200: '#EBD595',
                    300: '#DFBD5E',
                    400: '#D4A73A',
                    500: '#C08F25',
                    600: '#A3721D',
                    700: '#82571B',
                    800: '#6B461C',
                    900: '#5B3B1C',
                },
                cream: {
                    DEFAULT: '#FAF7F0',
                    dark: '#F2ECDF',
                },
            },
        },
    },

    plugins: [forms],
};
