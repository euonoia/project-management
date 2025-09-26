module.exports = {
  content: [
    "./*.php",                 // root PHP files
    "./logistics2/**/*.php",   // PHP inside logistics2
    "./reservation/**/*.php",  // (optional) if you want to include other folders
    "./fleetvehiclemanagement/**/*.php",
    "./dispatchsystem/**/*.php",
    "./src/**/*.{js,ts}",      // if you have JS/TS
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
     require('@tailwindcss/typography'),
  ],
}
