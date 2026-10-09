module.exports = {
  plugins: [
    // Bundles all @import files into one file
    require('postcss-import')(),
    // Fallbacks and prefixes for the browsers in "browserslist" (includes autoprefixer)
    require('postcss-preset-env')({
      stage: 2,
      features: {
        // All browsers in "defaults" support :is(); the fallback would only duplicate selectors
        'is-pseudo-class': false,
      },
    }),
    require('cssnano')(),
  ],
};
