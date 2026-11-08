# Upgrade

Changes of the Actra coding standard, newest first. ⚠️ marks changes that may make `composer check` of existing
projects fail or that change how projects work.

## v2.3.0 (2026-11-08)

- New rules in [AGENTS.md](AGENTS.md) for AI assistants and public repositories: final reports list only what the diff
  does not show; public repositories are described neutrally; reviewing sessions verify reported changes with
  `git status` and `git diff`; sub-sessions never change the index or working tree; `composer check` also for
  documentation-only changes; no project knowledge only in local assistant memory. No action needed in projects.

## v2.2.0 (2026-11-08)

- ⚠️ Never name the variadic parameter of a PHP built-in: `array_map(callback: $f, array: $a, arrays: $b)` throws an
  `ArgumentCountError` at runtime, and PHPStan did not report it ([php.md](standards/php.md), section 4). The new rule
  `actra.namedVariadicArgument` reports it. Fix findings by passing all arguments positionally
  (`array_map($f, $a, $b)`) or by spreading a list (`array_push($list, ...$values)`); do not add them to the baseline.
  Project tests that search for such calls (e.g. for `arrays:`) can be removed.

## v2.1.0 (2026-11-06)

- `UPDATE` and `DELETE` filter only by the primary key or a unique key; set-based changes first select the IDs, then
  update or delete `WHERE id IN (…)` in the same transaction. No `UPDATE … JOIN …`. Such statements break MariaDB's
  safe update mode (on by default in `actra/yuf`) only with production data volumes (error 1175)
  ([performance.md](standards/performance.md), section 2). Existing statements are migrated when their code is
  changed; never switch safe update mode off.

## v2.0.1 (2026-11-02)

- `vendor/bin/deploy main` (the `defaultRef` branch named explicitly) checks out the branch with a fast-forward like
  `vendor/bin/deploy`; it left the server checkout detached. Other branches stay detached.

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
