module.exports = {
  extends: ['stylelint-config-standard'],
  rules: {
    'alpha-value-notation': 'number',
    'max-nesting-depth': 3,
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: ['custom-media'],
      },
    ],
  },
};
