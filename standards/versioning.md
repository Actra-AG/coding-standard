# Versioning and Public API

## 1. Release cycle

- Prefer short development cycles: small changes, released soon after they are done, instead of collecting many
  changes for a big release. Small releases are easier to review, upgrade and roll back.
- A version is released with a commit: the commit that completes the change also adds its `UPGRADE.md` section with
  version and date (see section 6) and is tagged with that version.

## 2. Versions

Packages are versioned with Git tags in the format `vMAJOR.MINOR.PATCH`, based on
[Semantic Versioning](https://semver.org), with one deliberate difference: breaking changes do not force a new major
version.

- **major:** a major step of the package that brings many new features. Decided deliberately, never just because of
  a breaking change.
- **minor:** new features and/or breaking changes (may include fixes).
- **patch:** bug fixes only, no breaking changes.

Check the existing tags (`git tag --sort=-v:refname`) before suggesting the next version.

Because minor versions may contain breaking changes, a Composer constraint like `^4.7` also installs breaking changes.
Consumers read `UPGRADE.md` before running `composer update`, and `composer check` must be green afterwards.

## 3. Public API

In a library, everything a consumer can use is API:

- every public class, interface, method, constant and enum case,
- every argument **name** (consumers use named arguments) and argument order,
- return types and thrown exceptions,
- generated output (HTML markup, CSS classes, attributes, texts consumers compare against),
- documented behaviour.

Internal classes that are not meant for consumers are marked with `@internal`.

## 4. Breaking changes

A breaking change forces consumers to adapt their code or styling: renamed or removed class, method, argument or enum
case, changed signature or return type, documented behaviour that no longer works as before, changed HTML output.

- Breaking changes are allowed, but **no feature may be lost**: if something is removed, its replacement is
  documented.
- Every breaking change is listed in `UPGRADE.md`, marked with ⚠️, with a short before/after example.
- Breaking changes are released as minor version (or as part of a major version, see section 2).
- Prefer deprecating first (`@deprecated` with the replacement), when the old API can be kept with reasonable effort.
  Deprecated code may be removed in any following minor version; there is no need to wait for a major version.

## 5. Bug fixes

A bug fix that corrects clearly unintended behaviour (wrong results, exceptions, invalid SQL/HTML, security issues) is
not a breaking change, even if results change, as long as consumers need no code change. List noticeable fixes in
`UPGRADE.md` without ⚠️ and release them as patch (or as minor together with new features).

## 6. UPGRADE.md

- Libraries keep an `UPGRADE.md` with one section per version, newest first. Each section has the version and the
  release date (`YYYY-MM-DD`). There is no "unreleased" section: the section is added by the commit that releases the
  version.
- Projects without an `UPGRADE.md` decide with every change whether adding one would be useful, e.g. when the project
  gets consumers or when a change needs migration instructions.

````markdown
## v4.8.0 (2026-10-07)

### ⚠️ `FormField::create()` requires a `label`

Before:

```php
FormField::create(name: 'email');
```

After:

```php
FormField::create(name: 'email', label: 'Email address');
```

### `AmountParser::toInt()` returns `null` on overflow

Was `PHP_INT_MAX`. No code change needed.
````

## 7. Database and assets of libraries

- A library that owns database tables ships `db/schema.sql` (and `db/data.sql` for required rows) for new
  installations and one `db/updates/<version>.sql` per release that changes them. The `UPGRADE.md` section of that
  version names the update script. Schema changes are breaking changes for projects that query the tables directly.
- A library that ships CSS or JavaScript says in the `UPGRADE.md` section of every release that changes them whether
  projects must rebuild their bundles or republish the files.
