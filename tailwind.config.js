import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                paper: '#F7F7F3',
                ink: '#171B16',
                accent: {
                    DEFAULT: '#F2760C', // safety-orange: primary field-work actions, sunlight-visible
                    dark: '#C25D06',
                },
                field: {
                    DEFAULT: '#0B6E4F', // active/success — money, growth
                    dark: '#075038',
                },
                line: '#DEDED6',
                danger: '#C0392B',
            },
            borderRadius: {
                DEFAULT: '10px',
            },
        },
    },
    plugins: [],
};
