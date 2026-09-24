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
                    50: '#F1F8FF',
                    100: '#E0EFFF',
                    200: '#B9DCFF',
                    300: '#7CBDFF',
                    400: '#3E96F5',
                    500: '#1D74D3',
                    600: '#1763B8',
                    700: '#1559A6',
                    800: '#123B68',
                    900: '#0B1F3A',
                    950: '#081526',
                },
                success: {
                    50: '#F2FBF6',
                    100: '#E3F6EC',
                    200: '#C5ECd9',
                    300: '#93DCB2',
                    400: '#59C287',
                    500: '#33A46A',
                    600: '#258A58',
                    700: '#18794E',
                    800: '#1A6141',
                    900: '#175037',
                },
                warning: {
                    50: '#FFF9F0',
                    100: '#FFF0DC',
                    200: '#FFDDB5',
                    300: '#FFC482',
                    400: '#FB9F3B',
                    500: '#F4831D',
                    600: '#D96A12',
                    700: '#A15C00',
                    800: '#7D4A0E',
                    900: '#6A3F12',
                },
                danger: {
                    50: '#FEF4F3',
                    100: '#FDE8E7',
                    200: '#FAD3D1',
                    300: '#F5A9A5',
                    400: '#ED7A73',
                    500: '#E04F46',
                    600: '#CB4037',
                    700: '#B42318',
                    800: '#941F16',
                    900: '#7E1F18',
                },
                info: {
                    50: '#F2F9FC',
                    100: '#E0F1F8',
                    200: '#C0E2EF',
                    300: '#8ACBE4',
                    400: '#4DAECF',
                    500: '#2B93B8',
                    600: '#18779D',
                    700: '#0B6E99',
                    800: '#0E567A',
                    900: '#104868',
                },
            },
        },
    },

    plugins: [forms],
};
