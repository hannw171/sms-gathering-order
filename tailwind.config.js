import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#E3F2FD',
                    100: '#BBDEFB',
                    200: '#90CAF9',
                    500: '#2196F3',
                    600: '#1565C0', // Brand Primary
                    700: '#0D47A1', // Brand Primary Dark
                    800: '#00468C',
                    850: '#004D99',
                    900: '#0B1C30', // Navy Dark
                    950: '#090D16', // Stage Pitch Dark
                },
                canvas: {
                    DEFAULT: '#FAF8FF',
                    subtle: '#F8FAFC',
                },
                reward: {
                    gold: '#FBBF24',
                    amber: '#F59E0B',
                    glow: '#FCD34D',
                    emerald: '#10B981',
                    emeraldDark: '#059669',
                    tint: '#ECFDF5',
                },
                podium: {
                    gold: '#F59E0B',
                    silver: '#94A3B8',
                    bronze: '#CD7F32',
                },
            },
            fontFamily: {
                sans: ['Poppins', 'Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                poppins: ['Poppins', 'sans-serif'],
                jakarta: ['Plus Jakarta Sans', 'sans-serif'],
            },
            boxShadow: {
                'floating': '0 10px 25px -3px rgba(15, 23, 42, 0.15), 0 4px 6px -4px rgba(15, 23, 42, 0.10)',
                'drawer': '0 -8px 30px rgba(15, 23, 42, 0.12)',
                'stage-gold': '0 0 40px rgba(245, 158, 11, 0.40), 0 0 80px rgba(245, 158, 11, 0.15)',
                'stage-rank': '0 0 25px rgba(16, 185, 129, 0.35)',
            },
            maxWidth: {
                'guest': '448px',
            },
            borderRadius: {
                'xs': '4px',
                'sm': '6px',
                'md': '8px',
                'lg': '12px',
                'xl': '16px',
                '2xl': '20px',
                '3xl': '24px',
            },
        },
    },

    plugins: [forms],
};
