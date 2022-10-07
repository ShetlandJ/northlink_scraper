const defaultTheme = require('tailwindcss/defaultTheme');

const safeBgList = [100, 200, 300, 400, 500, 600, 700, 800, 900].map(number =>
    ['red', 'indigo', 'blue', 'green', 'yellow'].map(color => `bg-${color}-${number}`)
).flat()

const safeTextList = [100, 200, 300, 400, 500, 600, 700, 800, 900].map(number =>
    ['red', 'indigo', 'blue', 'green', 'yellow'].map(color => `text-${color}-${number}`)
).flat()

const safelist = [...safeBgList, ...safeTextList]

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
        screens: {
            'sm': '768px',
            // => @media (min-width: 640px) { ... }

            'md': '769px',

            'lg': '1024px',
        },
    },
    safelist,
    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
