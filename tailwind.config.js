/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#e0fafa',
          100: '#b3f3f5',
          200: '#7ee9ed',
          300: '#45dde6',
          400: '#14d2df',
          500: '#00bcd0',
          600: '#009aad',
          700: '#007a8a',
          800: '#005f6b',
          900: '#00424b',
        },
        accent: {
          400: '#ff7043',
          500: '#f4511e',
          600: '#e64a19',
        },
        danger: {
          500: '#e53935',
        }
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
