# Versioning and Public API

## 1. Versions

- Packages are versioned with Git tags following [Semantic Versioning](https://semver.org): `vMAJOR.MINOR.PATCH`.
    - **major:** breaking change
    - **minor:** new feature, backwards compatible (may include fixes)
    - **patch:** bug fix only
- Check the existing tags (`git tag --sort=-v:refname`) before suggesting the next version.

## 2. Public API

In a library, everything a consumer can use is API:

- every public class, interface, method, constant and enum case,
- every argument **name** (consumers use named arguments) and argument order,
- return types and thrown exceptions,
- generated output (HTML markup, CSS classes, attributes, texts consumers compare against),
- documented behaviour.

Internal classes that are not meant for consumers are marked with `@internal`.

## 3. Breaking changes

A breaking change forces consumers to adapt their code or styling: renamed or removed class, method, argument or enum
case, changed signature or return type, documented behaviour that no longer works as before, changed HTML output.

- Breaking changes are allowed, but **no feature may be lost**: if something is removed, its replacement is
  documented.
- Every breaking change is listed in `UPGRADE.md` in the topmost unreleased section, marked with ⚠️, with a short
  before/after example.
- Breaking changes are released as a new major version.
- Prefer deprecating first (`@deprecated` with the replacement) and removing in the next major version, when the old
  API can be kept with reasonable effort.

## 4. Bug fixes

A bug fix that corrects clearly unintended behaviour (wrong results, exceptions, invalid SQL/HTML, security issues) is
not a breaking change, even if results change, as long as consumers need no code change. List noticeable fixes in
`UPGRADE.md` without ⚠️ and release them as patch (or as minor together with new features).

## 5. UPGRADE.md

Libraries keep an `UPGRADE.md` with one section per version, newest first:

````markdown
## v5.0.0 (unreleased)

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
