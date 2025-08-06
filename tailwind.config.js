import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors'; // Import colors

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: { // ✅ All customizations must be inside `extend`
            fontFamily: {
                sans: ['IRANSans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: { // Indigo
                    50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa',
                    500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a', 950: '#172554',
                },
                secondary: { // Emerald
                    50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399',
                    500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b', 950: '#022c22',
                },
                // ✅ Semantic colors using aliases to existing Tailwind colors
                success: colors.green,
                warning: colors.yellow,
                danger: colors.red,
                info: colors.blue,
                // Custom semantic names for our project
                'surface-main': colors.white,
                'surface-secondary': colors.gray[50],
                'border-color': colors.gray[200],
                'text-main': colors.gray[800],
                'text-muted': colors.gray[600],
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
