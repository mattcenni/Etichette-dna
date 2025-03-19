/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./admin/**/*.php",
    "./includes/**/*.php",
    "./public/**/*.php"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
  important: true,
  corePlugins: {
    preflight: true,
  }
} 