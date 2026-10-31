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
| Own PHPStan rules ([src/phpstan/](../src/phpstan))                                | Named arguments in parameter order                       |
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
  bleeding edge, all strict parameters and the extensions listed above. It ignores
  `property.readOnlyAssignNotInConstructor` and `property.uninitializedReadonly`, because a native `readonly`
  property may be assigned once after construction (see [php.md](php.md), section 3); projects do not ignore them
  again.
  [config/disallowed.neon](../config/disallowed.neon) forbids the constructs banned by this standard.
  The own rules in [src/phpstan/](../src/phpstan) (autoloaded by Composer) report rules of this standard that no
  extension covers: `actra.namedArgumentOrder` reports named arguments that are not in the order of the parameters
  (see [php.md](php.md), section 4).
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
  the project root, `fnmatch()` patterns), not in the baseline (Craft projects: see [craft.md](craft.md),
  section 4):
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
  [config/php-cs-fixer.php](../config/php-cs-fixer.php) and adds the file header with
  [config/php-cs-fixer-header.php](../config/php-cs-fixer-header.php): `copyright:` (text after `@copyright`),
  `license:` of the project and `thirdParty:` (see [template](../templates/.php-cs-fixer.dist.php)).
- `header_comment` never overwrites the license of code adapted from third-party libraries (see [php.md](php.md),
  section 2). The header helper chooses the license per file by path, optionally limited to files containing a marker
  text, with PHP-CS-Fixer's (experimental) `RuleCustomisationPolicyInterface`.
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
- PHP-CS-Fixer only checks the paths of its `Finder`; the strict-types guard test checks every PHP file of the
  project (see [testing.md](testing.md), section 2).
- `.php-cs-fixer.cache` is not committed.

## 5. Local environment (DDEV)

- Projects provide a [DDEV](https://ddev.com) configuration (`.ddev/config.yaml`) with the required PHP version, so
  everybody runs the same environment.
- Without a matching local PHP, prefix the commands with `ddev`: `ddev composer check`.
- `.ddev/` contains no secrets; local overrides (`config.local.yaml`) are not committed.

## 6. Editor

- Every project has an `.editorconfig` (see [template](../templates/.editorconfig)).
- IDE project files (`.idea/`, `.vscode/`) are not committed, except shared settings agreed with the team.

## 7. Autoloading

- Composer loads all classes, also the project's own: PSR-4 in `composer.json`, `autoload` for application code and
  `autoload-dev` for tests. No second autoloader and no autoloader registration in the application or the tests.
- Entry points (`public/index.php`, CLI scripts) include `vendor/autoload.php` first. The test bootstrap only includes
  `vendor/autoload.php`. PHPStan needs no `scanDirectories` for own or Actra classes; generated classes are allowed
  (Craft: see [craft.md](craft.md), section 4).
- `composer dump-autoload --optimize --strict-psr` runs without warnings: file and class names match the namespace
  mapping.
- Composer is needed to build an application (`vendor/`), not on the server. Production uses an optimized
  autoloader (see [performance.md](performance.md), section 3).

## 8. Deployment

Applications are deployed with `vendor/bin/deploy` of this package, run on the developer's machine with their own SSH
keys. No manual `git pull` on the server, no CI/CD pipeline.

```bash
vendor/bin/deploy          # the defaultRef of deploy.json
vendor/bin/deploy v1.7.0   # a tag, branch or commit on origin (e.g. the previous commit for a rollback)
vendor/bin/deploy --yes    # without confirmation, for scripts
```

- **Steps:** locally, `deploy` fetches origin, resolves the ref (only commits on origin, never unpushed ones), reads the
  live commit on the server, shows both with the commits in between and asks for confirmation. Then, in one SSH call
  on the server, it stops if the working tree has local changes, runs `git fetch --tags`, checks out the commit (the
  `defaultRef` branch with a fast-forward, any other ref detached), runs
  `composer install --no-dev --optimize-autoloader --no-interaction`, deletes the `clearDirectories`, runs the `after`
  commands and appends a line to `deploy.log`. At the end it prints the live and the previous commit; a rollback is
  `vendor/bin/deploy <previous commit>`.
- **`main` is production:** by default `deploy` deploys `main` (`"defaultRef": "main"`), so `main` is always what runs
  in production and a forgotten tag never holds a release back. Only what may go live is merged to `main`; unfinished
  work and templates before the frontend review stay on a branch. Projects that deploy only released versions set
  `latest-tag`.
- **Configuration:** `deploy.json` in the project root, committed (see [template](../templates/deploy.json)). Unknown
  keys and invalid values stop the deployment.

  | Key                | Value                                                                                            |
  |:-------------------|:-------------------------------------------------------------------------------------------------|
  | `host`             | SSH alias of the server (see below)                                                              |
  | `path`             | Absolute path of the project root on the server (the Git checkout)                               |
  | `defaultRef`       | A branch, by default `main`, or `latest-tag` (highest `vMAJOR.MINOR.PATCH` tag, no pre-releases) |
  | `clearDirectories` | Globs relative to the project root, deleted after `composer install` (`["app/cache/v[0-9]*/"]`)  |
  | `after`            | Optional shell commands, run in the project root at the end                                      |

  `clearDirectories` never leave the project root: no absolute paths, no `.`, `..` or `.git` segments, no empty
  globs, the first directory named without wildcards; matches outside the root (symbolic links) stop the deployment.
  In yuf projects use `app/cache/v[0-9]*/` (compiled templates, `v` + format version), never `app/cache/v*/`: that also
  matches `app/cache/values/`, yuf's `FileCache`.
- **SSH:** `host` is an alias of `~/.ssh/config` with `HostName`, `User`, `Port` and `IdentityFile`. Every developer
  who deploys defines the same alias; user, port and key never go into the project.
- **Server:** a Git checkout of the project with read access to origin (e.g. a deploy key), bash (4.2 or newer, or
  the `date` command), Git, Composer and PHP on the `PATH` of a login shell. The web server's document root is
  `public/`, so `.git/`, `.env.php`, `deploy.log` and everything else outside `public/` is never reachable from the
  web. Nobody edits files of the checkout on the server; server-specific files are ignored by Git.
- **Log:** `deploy.log` in the project root gets one tab-separated line per deployment: UTC time, `ok` or `failed`,
  ref, live commit, previous commit, Git user of the developer. The project ignores it in `.gitignore` (`/deploy.log`;
  a whitelist `.gitignore` already does); `deploy` refuses to run otherwise.
- **Local requirements:** bash 3.2 or newer (macOS default), Git, SSH and [jq](https://jqlang.org) (included in macOS
  15 and newer).
- Before deploying, update the Actra dependencies and make `composer check` green (see
  [versioning.md](versioning.md), section 8). AI assistants never deploy on their own.
