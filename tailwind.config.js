import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Habilitar el modo oscuro basado en clases
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                //sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                adult: ['Times New Roman', 'serif'],
                young: ['Comic Sans MS', 'cursive'],
                kid: ['Papyrus', 'fantasy'],
            },
            colors: {
                adult: '#000000',
                young: '#2c2c2c',
                kid: '#0055ff',
            },
        },
    },

    plugins: [forms, require('flowbite/plugin')],
};
