/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        maroon: {
          DEFAULT: '#6B0F1A',
          dark:    '#4A0A12',
          light:   '#8B1A28',
        },
        gold: {
          DEFAULT: '#C9A84C',
          light:   '#E8C97A',
          pale:    '#F5EDD3',
        },
      },
      fontFamily: {
        sans:  ['var(--font-dm-sans)', 'ui-sans-serif', 'system-ui'],
        serif: ['var(--font-playfair)', 'Georgia', 'serif'],
      },
      borderRadius: {
        xl:  '12px',
        '2xl': '16px',
      },
    },
  },
  plugins: [],
};
