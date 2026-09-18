
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
            colors: {
                primary: {
                    DEFAULT: '#167C80',
                    dark: '#0F5F63',
                    soft: '#E8F5F4',
                },
                accent: {
                    DEFAULT: '#C62828',
                    dark: '#A61F1F',
                    soft: '#FCEAEA',
                },
                navy: '#172B3A',
                background: '#F5F7F8',
                surface: '#FFFFFF',
                muted: '#64748B',
                border: '#D9E2E5',
            },
            boxShadow: {
                soft: '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.03)',
                'soft-md': '0 8px 30px -4px rgba(0, 0, 0, 0.07), 0 4px 10px -2px rgba(0, 0, 0, 0.04)',
                'soft-lg': '0 15px 40px -6px rgba(0, 0, 0, 0.09), 0 6px 16px -4px rgba(0, 0, 0, 0.05)',
                card: '0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px 0 rgba(0, 0, 0, 0.02)',
            },
            borderRadius: {
                xl: '0.875rem',
                '2xl': '1.125rem',
                '3xl': '1.5rem',
            }
        },
    },

    plugins: [forms],
};
