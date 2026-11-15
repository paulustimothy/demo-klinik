/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f0f9fa',
          100: '#d7eff2',
          200: '#b2dfe5',
          300: '#7ec6d1',
          400: '#3fa3b4',
          500: '#0d7480', // Primary Teal
          600: '#0b626d',
          700: '#09515b',
          800: '#0a424a',
          900: '#0c383e',
        },
        clinic: {
          bg: '#f7f9fa',
          surface: '#ecf0f2',
          card: '#ffffff',
          ink: '#1f3038',
          muted: '#5d6b72',
          border: '#dbe3e6',
          dark: '#142127',
        }
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
      },
      borderRadius: {
        'card': '14px',
      },
      boxShadow: {
        'soft': '0 2px 10px rgba(31, 48, 56, 0.05)',
        'lift': '0 8px 24px rgba(31, 48, 56, 0.08)',
      }
    },
  },
  plugins: [],
};
