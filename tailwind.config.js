import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'bg-dinas-green',
        'bg-dinas-greenhover',
        'hover:bg-dinas-greenhover',
        'text-dinas-gold',
        'border-dinas-gold',
        'bg-purple-600',
        'bg-purple-700',
        'hover:bg-purple-700',
        'bg-blue-500',
        'bg-blue-600',
        'bg-blue-700',
        'hover:bg-blue-700',
        'bg-green-500',
        'bg-green-600',
        'bg-green-700',
        'hover:bg-green-700',
        'text-white',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                dinas: {
                    green: '#0A773A',
                    greenhover: '#085C2D',
                    gold: '#F8C72C',
                }
            },
        },
    },

    plugins: [forms],
};
