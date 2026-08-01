import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
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
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                display: ['Sora', 'Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            /*
             * Design tokens — palette sémantique.
             * Niveaux 600+ : contraste AA garanti sur fond blanc.
             */
            colors: {
                primary: colors.blue, // Marque : #2563EB (= primary-600)
                accent: colors.sky, // Dégradés et accents secondaires
                success: colors.emerald,
                warning: colors.amber,
                danger: colors.red,
                info: colors.cyan,
            },
        },
    },

    plugins: [forms],
};
