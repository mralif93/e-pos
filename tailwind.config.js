const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/views/pos/**/*.blade.php",
  ],
  safelist: [
    // Core colour utilities — bg, text, border, ring, shadow
    {
      pattern: /(bg|text|border|ring|shadow|outline)-(indigo|orange|rose|emerald|blue|violet|amber|teal|cyan|green|red|slate|gray)-(50|100|200|300|400|500|600|700|800|900)/,
      variants: ['hover', 'focus', 'active', 'group-hover', 'disabled', 'focus-within'],
    },
    // Gradient utilities (from / via / to)
    {
      pattern: /(from|via|to)-(indigo|orange|rose|emerald|blue|violet|amber|teal|cyan|green|red|slate|gray)-(50|100|200|300|400|500|600|700|800|900)/,
      variants: ['hover'],
    },
    // Shadow with opacity (e.g. shadow-indigo-200/50)
    {
      pattern: /shadow-(indigo|orange|rose|emerald|blue|violet|amber|teal|cyan|green|red|slate|gray)-(100|200|300|400|500)\/(30|40|50|60)/,
    },
    // Ring with opacity
    {
      pattern: /ring-(indigo|orange|rose|emerald|blue|violet|amber|teal|cyan|green|red|slate|gray)-(200|300|400|500)\/(20|30|40|50)/,
    },
  ],
  theme: {
    extend: {
      fontSize: {
        'xs': ['var(--text-xs)', '1.5'],
        'sm': ['var(--text-sm)', '1.5'],
        'base': ['var(--text-base)', '1.5'],
        'md': ['var(--text-md)', '1.2'],
        'lg': ['var(--text-lg)', '1.2'],
        'xl': ['var(--text-xl)', '1.2'],
        '2xl': ['var(--text-xl)', '1.2'], // Automatically scales down old 2xl titles
        '3xl': ['calc(var(--text-xl) * 1.25)', '1.2'],
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        primary: {
          DEFAULT: '#4f46e5',
          '50': '#eef2ff',
          '100': '#e0e7ff',
          '200': '#c7d2fe',
          '300': '#a5b4fc',
          '400': '#818cf8',
          '500': '#6366f1',
          '600': '#4f46e5',
          '700': '#4338ca',
          '800': '#3730a3',
          '900': '#312e81',
        },
        secondary: {
          DEFAULT: '#1f2937',
          '50': '#f9fafb',
          '100': '#f3f4f6',
          '200': '#e5e7eb',
          '300': '#d1d5db',
          '400': '#9ca3af',
          '500': '#6b7280',
          '600': '#4b5563',
          '700': '#374151',
          '800': '#1f2937',
          '900': '#111827',
        },
        accent: {
          DEFAULT: '#10b981',
          '50': '#f0fdf4',
          '100': '#dcfce7',
          '200': '#bbf7d0',
          '300': '#86efac',
          '400': '#4ade80',
          '500': '#22c55e',
          '600': '#16a34a',
          '700': '#15803d',
          '800': '#166534',
          '900': '#14532d',
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}

