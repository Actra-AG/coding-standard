# Upgrade

Changes of the Actra coding standard, newest first. ⚠️ marks changes that may make `composer check` of existing projects
fail or that change how projects work.

## v1.8.0 (2026-10-09)

### Commit type `content`, short commit messages, code in answers

- [git.md](standards/git.md), section 1: new type `content` for editorial changes in websites and CMS projects without
  code change. Topics like SEO are a scope (`content(seo): …`, `feat(seo): …`), not an own type. The body may be
  omitted if the subject already says everything; bullets do not repeat the subject or list every touched file. The
  `Attention:` paragraph is required for breaking changes.
- [AGENTS.md](AGENTS.md), "Response style": show only the changed code blocks with their file path, explain with short
  inline comments instead of long markdown paragraphs.

No code change needed. Projects remove these rules and own commit types (e.g. `seo`) from their `AGENTS.md`; existing
commits stay as they are.

## v1.7.0 (2026-10-08)

### ⚠️ Libraries lock the minor version of their Actra dependencies

[versioning.md](standards/versioning.md), section 8: a library that depends on a package with breaking changes in
minor versions requires it with `~X.Y.Z` (patches only) instead of `^X.Y`, and raises it with its own release. With
`^`, `composer update` in a consuming project installed a newer minor version that the library did not support yet
(e.g. a new abstract method). Applications keep `^`.

Before (library): `"actra/yuf": "^4.37"`. After (library): `"actra/yuf": "~4.37.0"`.

## v1.6.0 (2026-10-08)

### CSS, external JavaScript libraries, raising dependencies

- [html-javascript.md](standards/html-javascript.md), section 3: plain CSS with custom properties, one file per block
  imported by one entry file, a preprocessor or build step only with a reason; section 2: no external JavaScript
  library without a reason.
- [versioning.md](standards/versioning.md), section 8: read the `UPGRADE.md` of a dependency before raising it, use the
  lowest version with the used API as lower bound and check against exactly that version, raise a dependency with many
  releases in steps.

No code change needed; projects with a CSS preprocessor or JavaScript libraries name the reason in their `AGENTS.md`.

## v1.5.0 (2026-10-08)

### Global rules first

[AGENTS.md](AGENTS.md), "Working on a task": before a rule is added to a project, it is decided whether it applies to
every Actra project; such rules go into the coding standard, the project keeps only its specific part. No code change
needed.

## v1.4.1 (2026-10-08)

[security.md](standards/security.md), section 1: the example for an explicit unsafe variant names an existing API
(`HtmlText::fromHtml()` next to `HtmlText::fromText()`) instead of `HtmlText::unencoded()`. No code change needed.

## v1.4.0 (2026-10-08)

### Texts and translations

New [standards/i18n.md](standards/i18n.md): no hard-coded user-visible texts, plain-text messages escaped on output,
named placeholders that are the same in every language, the language of a request chosen once and texts for another
person in that person's language. Existing hard-coded texts are moved when their code is changed.

### Libraries: database update scripts, asset notes, checks in a consuming project

[versioning.md](standards/versioning.md), section 7: libraries with database tables ship `schema.sql` and one
`db/updates/<version>.sql` per changing release; libraries with assets say in `UPGRADE.md` whether projects must
rebuild their bundles. [testing.md](standards/testing.md): libraries without an own app check views and assets in a
consuming project with a Composer path repository. No code change needed.

## v1.3.0 (2026-10-08)

### Opt-in PHPStan rule against superglobals

New [config/phpstan-no-superglobals.neon](config/phpstan-no-superglobals.neon) reports `$_GET`, `$_POST`, `$_COOKIE`,
`$_FILES`, `$_SERVER` and `$_SESSION`, for projects whose framework has request and session objects. Files that must
read superglobals (entry points, the framework's request factory) are allowed with `actraSuperglobalsAllowIn` (see
[tooling.md](standards/tooling.md), section 3). It is not included by default: no change for existing projects.

## v1.2.0 (2026-10-07)

### ⚠️ Settings bundles end with `Settings`, without `Model` suffix

The `Model` suffix for settings bundles is removed (see [naming.md](standards/naming.md)): "Settings" already names the
role, and "Model" suggests a domain or ORM model. Value objects have no type suffix.

Before:

```php
final readonly class SessionSettingsModel {}
```

After:

```php
final readonly class SessionSettings {}
```

Existing names are renamed when their code is changed (see [naming.md](standards/naming.md), section 4). In public
libraries this is a breaking change: keep the old name as deprecated alias for one release.

### External interfaces keep their names

Interfaces and traits of the project have no suffix; external interfaces such as the PSR interfaces
(`LoggerInterface`, `ClockInterface`) are implemented directly with their own names, not wrapped to drop the suffix.
No code change needed.

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
