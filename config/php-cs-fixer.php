<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

/**
 * PHP-CS-Fixer rules of the Actra coding standard (PER Coding Style, strict types, PHP 8.5 migration).
 * Used by the .php-cs-fixer.dist.php of the project, which adds the file header (see templates/).
 *
 * @return array<string, bool|array<string, mixed>>
 */
return [
    '@PER-CS' => true,
    '@PER-CS:risky' => true,
    '@PHP8x5Migration' => true,
    '@PHP8x5Migration:risky' => true,
    'declare_strict_types' => true,
    // These fixers ignore named arguments and produce invalid code, e.g. `in_array(needle: $a, haystack: $b, true)`
    // or `(int) (value: $a)`, or they change the behaviour, e.g. `$float == 0` to `$float === 0`. PHPStan reports
    // missing `strict` parameters, loose comparisons and `rand()`; fix them by hand (see standards/tooling.md).
    'modernize_types_casting' => false,
    'pow_to_exponentiation' => false,
    'random_api_migration' => false,
    'strict_comparison' => false,
    'strict_param' => false,
    'is_null' => true,
    'yoda_style' => [
        'equal' => false,
        'identical' => false,
        'less_and_greater' => false,
    ],
    'no_alias_functions' => true,
    'nullable_type_declaration' => ['syntax' => 'question_mark'],
    'nullable_type_declaration_for_default_null_value' => true,
    'void_return' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'no_unused_imports' => true,
    'ordered_imports' => [
        'imports_order' => ['class', 'function', 'const'],
        'sort_algorithm' => 'alpha',
    ],
    'single_quote' => true,
    'trailing_comma_in_multiline' => [
        'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
    ],
    'no_empty_comment' => true,
    'no_empty_phpdoc' => true,
    'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
    'phpdoc_trim' => true,
];
