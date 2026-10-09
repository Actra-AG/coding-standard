module.exports = {
  singleQuote: true,
  arrowParens: 'avoid',
  overrides: [
    {
      files: ['**/*.css'],
      options: {
        singleQuote: false,
      },
    },
  ],
};
