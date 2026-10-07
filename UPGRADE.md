# Upgrade

Changes of the Actra coding standard, newest first. ⚠️ marks changes that may make `composer check` of existing projects
fail or that change how projects work.

## v1.1.1 (2026-10-07)

### PHP-CS-Fixer no longer breaks named arguments

`strict_param`, `strict_comparison`, `modernize_types_casting`, `pow_to_exponentiation` and `random_api_migration` are
disabled. They ignore named arguments (PHP-CS-Fixer 3.95.27) and produced invalid code or changed the behaviour:

```php
in_array(needle: $a, haystack: $b);  // became in_array(needle: $a, haystack: $b, true): fatal error
intval(value: $a);                   // became (int) (value: $a): parse error
pow(num: 1024, exponent: $b);        // became (num: 1024)**( exponent: $b): parse error
$float == 0;                         // became $float === 0: always false
```

PHPStan still reports missing `strict` parameters, loose comparisons and `rand()`; fix them by hand. No code change
needed. Projects that already ran `composer cs:fix` with v1.1.0 or older check the changed calls and comparisons (see
[tooling.md](standards/tooling.md), section 4).

## v1.1.0 (2026-10-07)

### ⚠️ Breaking changes no longer require a major version

Breaking changes are released as minor versions; a major version marks a major step with many new features. Deprecated
code may be removed in any following minor version. Projects read `UPGRADE.md` of their dependencies before
`composer update` (see [versioning.md](standards/versioning.md)).

### ⚠️ `UPGRADE.md` sections have a release date instead of "unreleased"

The commit that releases a version adds its section with version and date. Projects without `UPGRADE.md` decide with
every change whether adding one would be useful.

### ⚠️ Linear Git history

No merge commits: rebase branches onto `main` and integrate them with fast-forward or "Rebase and merge" (see
[git.md](standards/git.md)).

### Short development cycles

Prefer small changes that are released soon.

## v1.0.0 (2026-10-07)

First release: global `AGENTS.md`, standards, shared PHPStan and PHP-CS-Fixer configuration, templates.
