# Tooling

## 1. The `actra/coding-standard` package

Every project installs this repository as a development dependency:

```bash
composer require --dev actra/coding-standard
```

It brings the rules for developers and AI assistants (`vendor/actra/coding-standard/AGENTS.md`) and the tools that
enforce them, with a shared configuration:

| Tool                                                                              | Purpose                                                  |
|:----------------------------------------------------------------------------------|:---------------------------------------------------------|
| [PHPStan](https://phpstan.org)                                                    | Static analysis, level 10, bleeding edge                 |
| [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules)           | Strict comparisons, no `empty()`, booleans in conditions |
| [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) | Reports usage of deprecated code                         |
| [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit)                     | Correct types and checks for PHPUnit tests               |
| [phpstan-disallowed-calls](https://github.com/spaze/phpstan-disallowed-calls)     | Forbids `isset()`, `is_null()`, `switch`, insecure calls |
| [PHP-CS-Fixer](https://cs.symfony.com)                                            | PER Coding Style, file header, strict types              |

PHPUnit is installed by the project itself (`composer require --dev phpunit/phpunit`), because its version depends on
the project. Development tools are never runtime requirements. Additional tools need a reason and are documented in
the project's `AGENTS.md`.

## 2. Composer scripts

Every project defines these scripts in `composer.json`:

```json
"scripts": {
  "cs": "php-cs-fixer check --diff",
  "cs:fix": "php-cs-fixer fix",
  "phpstan": "phpstan analyse --memory-limit=-1",
  "phpstan:baseline": "phpstan analyse --memory-limit=-1 --generate-baseline",
  "test": "phpunit",
  "check": [
    "@cs",
    "@phpstan",
    "@test"
  ]
},
"scripts-descriptions": {
  "cs": "Check the code style (PER Coding Style)",
  "cs:fix": "Fix the code style",
  "phpstan": "Run the static analysis (PHPStan level 10, strict)",
  "phpstan:baseline": "Regenerate phpstan-baseline.neon",
  "test": "Run all tests",
  "check": "Check the code style, run the static analysis and all tests"
}
```

```bash
composer cs                # code style check
composer cs:fix            # fix the code style
composer phpstan           # static analysis
composer phpstan:baseline  # regenerate phpstan-baseline.neon
composer test              # all tests
composer check             # cs + phpstan + test
```

Every task and every commit must end with a green `composer check`.

## 3. PHPStan

- `phpstan.neon` in the project root includes the shared configuration
  [config/phpstan.neon](../config/phpstan.neon) (see [template](../templates/phpstan.neon)). It sets `level: 10`,
  bleeding edge, all strict parameters and the extensions listed above.
  [config/disallowed.neon](../config/disallowed.neon) forbids the constructs banned by this standard.
- The project only adds `phpVersion` (matching the minimum PHP version), the analysed paths and, if needed, the
  baseline. Analysed paths include all PHP code (`src/`, `tests/`, application code). Only generated code (caches,
  generated data files) is excluded. Do not lower the level, disable rules or add exclusions for hand-written code.
- **Baseline for legacy code:** existing errors go into `phpstan-baseline.neon`.
    - New files must not appear in the baseline. `tests/` never has baseline entries.
    - When you change an existing file, fix its baseline entries and regenerate the baseline. The baseline may only
      shrink.
    - `@phpstan-ignore` is only allowed with an identifier and a reason, e.g.
      `// @phpstan-ignore argument.type (PDO returns mixed, value validated above)`.
- A deliberately allowed exception of a disallowed call (e.g. reading `$_POST` in the request layer of a framework) is
  configured with `allowIn` for the specific path in the project's `phpstan.neon`, never with a global switch.
- **Opt-in: no superglobals.** Projects whose framework has request and session objects (yuf, Craft CMS / Yii,
  Symfony) also include [config/phpstan-no-superglobals.neon](../config/phpstan-no-superglobals.neon). It reports
  `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`, `$_SERVER` and `$_SESSION` (identifier `disallowed.variable`);
  `$GLOBALS` and `$_REQUEST` are disallowed in every project. Not for WordPress plugins, legacy code without request
  objects or plain scripts. The few files that must read superglobals (entry points such as `public/index.php`, the
  framework's own request factory or session handler) are listed in `actraSuperglobalsAllowIn` (paths relative to
  the project root, `fnmatch()` patterns), not in the baseline:
  ```neon
  includes:
      - vendor/actra/coding-standard/config/phpstan.neon
      - vendor/actra/coding-standard/config/phpstan-no-superglobals.neon

  parameters:
      actraSuperglobalsAllowIn:
          - public/index.php
          - src/Http/HttpRequest.php
  ```

## 4. PHP-CS-Fixer

- `.php-cs-fixer.dist.php` in the project root uses the shared rule set
  [config/php-cs-fixer.php](../config/php-cs-fixer.php) and adds the file header with the license of the project (see
  [template](../templates/.php-cs-fixer.dist.php)).
- The rule set implements PER Coding Style (risky rules included), `declare(strict_types=1);`, `=== null` instead of
  `is_null()`, no Yoda conditions, ordered and unused imports, trailing commas in multi-line lists and the PHP 8.5
  migration rules.
- Fixers that rewrite function calls or comparisons without supporting named arguments or without knowing the types
  are disabled: `strict_param`, `strict_comparison`, `modernize_types_casting`, `pow_to_exponentiation`,
  `random_api_migration`. PHPStan reports missing `strict` parameters, loose comparisons (`==`) and insecure calls
  (`rand()`); fix them by hand with named arguments and the correct comparison for the type, e.g.
  `in_array(needle: $id, haystack: $ids, strict: true)` or `$amount === 0.0`.
- Risky fixers change code, not only its formatting. Review the diff of `composer cs:fix` like any other code change and
  run `composer check` afterwards (PHPStan also reports code that does not compile, e.g. a positional argument after a
  named argument). Do not commit files that PHP-CS-Fixer skipped because of lint errors.
- Do not disable rules in the project. If a rule is wrong for all projects, change it here.
- `.php-cs-fixer.cache` is not committed.

## 5. Local environment (DDEV)

- Projects provide a [DDEV](https://ddev.com) configuration (`.ddev/config.yaml`) with the required PHP version, so
  everybody runs the same environment.
- Without a matching local PHP, prefix the commands with `ddev`: `ddev composer check`.
- `.ddev/` contains no secrets; local overrides (`config.local.yaml`) are not committed.

## 6. Editor

- Every project has an `.editorconfig` (see [template](../templates/.editorconfig)).
- IDE project files (`.idea/`, `.vscode/`) are not committed, except shared settings agreed with the team.
