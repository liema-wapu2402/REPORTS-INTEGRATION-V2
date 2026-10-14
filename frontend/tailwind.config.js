/** @type {import('tailwindcss').Config} */
const v = (name) => `rgb(var(--${name}) / <alpha-value>)`

export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        bg: v('bg'),
        panel: v('panel'),
        line: v('line'),
        ink: v('ink'),
        mute: v('mute'),
        accent: v('accent'),
        accent2: v('accent2'),
        ok: v('ok'),
        warn: v('warn'),
        bad: v('bad'),
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
        sans: ['"IBM Plex Sans"', 'system-ui', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
      },
      keyframes: {
        rise: { '0%': { opacity: '0', transform: 'translateY(14px)' }, '100%': { opacity: '1', transform: 'none' } },
        float: { '0%,100%': { transform: 'translate3d(0,0,0)' }, '50%': { transform: 'translate3d(30px,-40px,0)' } },
        scan: { '0%': { transform: 'translateY(-10vh)' }, '100%': { transform: 'translateY(110vh)' } },
        gridmove: { '0%': { backgroundPosition: '0 0' }, '100%': { backgroundPosition: '48px 48px' } },
        ring: { '0%': { transform: 'scale(.8)', opacity: '.7' }, '100%': { transform: 'scale(2.2)', opacity: '0' } },
        dash: { to: { strokeDashoffset: '-24' } },
        shimmer: { '0%': { backgroundPosition: '-200% 0' }, '100%': { backgroundPosition: '200% 0' } },
        blink: { '0%,100%': { opacity: '1' }, '50%': { opacity: '.25' } },
      },
      animation: {
        rise: 'rise .6s cubic-bezier(.2,.7,.2,1) both',
        float: 'float 18s ease-in-out infinite',
        scan: 'scan 9s linear infinite',
        gridmove: 'gridmove 6s linear infinite',
        ring: 'ring 2s ease-out infinite',
        dash: 'dash 1.2s linear infinite',
        shimmer: 'shimmer 2.2s linear infinite',
        blink: 'blink 1.4s ease-in-out infinite',
      },
    },
  },
  plugins: [],
}
