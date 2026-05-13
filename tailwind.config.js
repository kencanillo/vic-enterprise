module.exports = {
  content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],
  theme: {
    extend: {
      colors: { navy: '#001E40', gold: '#FDC003', surface: '#F8F9FA' },
      fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Manrope', 'sans-serif'] },
    },
  },
  plugins: [require('@tailwindcss/forms')],
};