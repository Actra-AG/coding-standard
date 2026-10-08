# Actra Coding Standard

Shared coding standard for all PHP projects and libraries of [Actra AG](https://www.actra.ch), for developers and AI
assistants alike. It builds on the PHP-FIG standards (PSR, PER Coding Style) and adds stricter rules for types,
security and testing, enforced with a shared PHPStan (level 10, strict) and PHP-CS-Fixer configuration.

This repository is public. It contains rules only, never project data, credentials, hostnames or customer names.

## Contents

| Document                                                     | Topic                                                          |
|:-------------------------------------------------------------|:---------------------------------------------------------------|
| [AGENTS.md](AGENTS.md)                                       | Working rules for developers and AI assistants (entry point)   |
| [standards/formatting.md](standards/formatting.md)           | PSR / PER Coding Style, spacing, line length, files            |
| [standards/naming.md](standards/naming.md)                   | Namespaces, classes, enums, methods, variables, tests          |
| [standards/php.md](standards/php.md)                         | Structure, types, enums, style, exceptions, comments           |
| [standards/security.md](standards/security.md)               | Validation, sanitizing, escaping, SQL, CSRF, sessions, secrets |
| [standards/html-javascript.md](standards/html-javascript.md) | Generated HTML, accessibility, JavaScript                      |
| [standards/i18n.md](standards/i18n.md)                       | Texts and translations, placeholders, language at runtime      |
| [standards/testing.md](standards/testing.md)                 | Test layout, what to test, test doubles                        |
| [standards/tooling.md](standards/tooling.md)                 | PHPStan, PHPUnit, Composer scripts, DDEV                       |
| [standards/versioning.md](standards/versioning.md)           | Release cycle, versions, breaking changes, `UPGRADE.md`        |
| [standards/git.md](standards/git.md)                         | Commit messages, `.gitignore`, review before commit            |
| [config/](config)                                            | Shared PHPStan and PHP-CS-Fixer configuration                  |
| [templates/](templates)                                      | Starter files for projects (`AGENTS.md`, `CLAUDE.md`, configs) |

## Binding rules

- The rules apply to all new and changed code. Existing code that does not meet them yet is brought up to the standard
  when it is changed ("leave it cleaner than you found it").
- Imperative rules ("do", "never", "always") are mandatory. "Prefer" and "should" mark defaults: deviate only with a
  reason that you can name in the code review.
- Projects may **add** stricter or project-specific rules in their own `AGENTS.md`, but do not weaken or repeat the
  rules defined here. If a project must deviate, it says so explicitly in its own `AGENTS.md`, with the reason.

## Using the standard in a project

1. Install the package as development dependency (it brings PHPStan with extensions and PHP-CS-Fixer):
   ```bash
   composer require --dev actra/coding-standard
   ```
2. Copy the files from [templates/](templates) into the project root and adapt them:
    - `AGENTS.md`: fill in the project-specific part,
    - `CLAUDE.md`: imports the global and the project `AGENTS.md`,
    - `phpstan.neon`, `.php-cs-fixer.dist.php` (license in the header), `.editorconfig`.
3. Add the Composer scripts from [tooling.md](standards/tooling.md) and make `composer check` green (use a PHPStan
   baseline for legacy code).
4. Projects whose framework has request and session objects also include
   [config/phpstan-no-superglobals.neon](config/phpstan-no-superglobals.neon) (see [tooling.md](standards/tooling.md),
   section 3).
5. Remove rules from the project documentation that are already covered here.

## Releases

Versions are Git tags (see [versioning.md](standards/versioning.md)); changes are listed in [UPGRADE.md](UPGRADE.md).
Stricter rules that make `composer check` of existing projects fail are breaking changes and are marked with ⚠️ there.
Update projects with `composer update actra/coding-standard`.

## Changing the standard

Changes are proposed as pull requests. A rule needs a short reason, and it must be applicable to all projects;
project-specific rules stay in the project. Use [Conventional Commits](standards/git.md) for the commit messages.

## License

[MIT](LICENSE)
