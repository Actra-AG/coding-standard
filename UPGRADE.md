# Upgrade

Changes of the Actra coding standard, newest first. ⚠️ marks changes that may make `composer check` of existing
projects fail or that change how projects work.

## v2.0.0 (2026-10-31)

- ⚠️ Raise the constraint to `"actra/coding-standard": "^2.0"` (`composer require --dev actra/coding-standard:^2.0`);
  `^1.x` does not install it. The major version marks the end of the build phase of the new Actra base: releases
  follow short development cycles again ([versioning.md](standards/versioning.md), section 1). The changes of v1 are
  in [docs/upgrade/v1.md](docs/upgrade/v1.md).
- ⚠️ Named arguments follow the parameter order of the called function ([php.md](standards/php.md), section 4).
  PHPStan reports a different order with the new rule `actra.namedArgumentOrder`, the first own rule of the standard
  (in `src/phpstan/`, autoloaded by Composer, enabled by `config/phpstan.neon`). Fix findings by reordering the named
  arguments as the message says; do not add them to the baseline.
- The [template](templates/.php-cs-fixer.dist.php) of `.php-cs-fixer.dist.php` declares the type of the header
  helper, so PHPStan can analyse the file. Projects that list it in their PHPStan `paths` copy the `@var` block and
  the `$headerFactory` lines.

## Older versions

- [v1](docs/upgrade/v1.md)
