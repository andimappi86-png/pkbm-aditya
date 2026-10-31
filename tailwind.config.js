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
                display: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                'serif-editorial': ['Instrument Serif', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // ============================================================
                // DESIGN TOKENS: Harmonized with official PKBM Aditya logo
                // Primary: Logo Cyan/Teal (#5DB8B3)
                // Accent:  Logo Crimson Red (#CE0C0D / #CB0F10)
                // Neutrals: Cool slate/teal tinted white & canvas
                // ============================================================
                brand: {
                    DEFAULT: '#0E706A',       // Primary teal accessible (4.9:1 on white)
                    hover: '#094F4B',         // Primary teal hover (7.2:1 on white)
                    light: '#5DB8B3',         // Exact mid-tone logo cyan/teal
                    subtle: '#E8F5F4',        // Soft surface tint
                    border: '#CCE8E6',        // Subtle border
                },
                'brand-accent': {
                    DEFAULT: '#C51618',       // Logo crimson red accessible (5.5:1 on white)
                    hover: '#9E1214',         // Logo crimson hover
                    light: '#CE0C0D',         // Exact logo crimson bright
                    subtle: '#FDF2F2',        // Soft red surface tint
                    border: '#FCA5A5',        // Soft red border
                },
                'brand-dark': {
                    DEFAULT: '#091716',       // Deep cool logo slate dark (surfaces/footer)
                    alt: '#0D211F',           // Dark panel alternative
                    card: '#122D2B',          // Dark card surface
                },

                // Compatibility aliases harmonized to logo palette
                'warm-ivory': '#F8FAFA',      // Clean cool logo canvas (replaces yellowish ivory)
                'warm-sand': '#EEF5F5',       // Cool subtle surface (replaces yellowish sand)
                'deep-forest': '#091716',     // Cool logo dark (replaces olive forest)
                'deep-forest-alt': '#0D211F', // Cool logo dark alt
                'pine-teal': '#0E706A',       // Aligned to logo primary
                'pine-teal-hover': '#094F4B', // Aligned to logo hover
                'terracotta': '#C51618',      // Aligned to logo crimson red (replaces orange terracotta)
                'terracotta-hover': '#9E1214',// Aligned to logo crimson hover
                'warm-border': '#D8E4E3',     // Crisp cool border (replaces yellow warm border)

                // Semantic alias tokens
                primary: {
                    DEFAULT: '#0E706A',
                    dark: '#094F4B',
                    soft: '#E8F5F4',
                },
                accent: {
                    DEFAULT: '#C51618',
                    dark: '#9E1214',
                    soft: '#FDF2F2',
                },
                navy: '#0E1A19',
                background: '#F8FAFA',
                surface: '#FFFFFF',
                muted: '#4D5E5C',
                border: '#D8E4E3',
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
            },
        },
    },

    plugins: [forms],
};