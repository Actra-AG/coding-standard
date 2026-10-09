# PHP

Target version: the current PHP release (at the time of writing PHP 8.5). Each project states its minimum version in
`composer.json`.

## 1. Structure

- **One class, one purpose.** The name says what it does. If you need "and" to describe it, split it.
- Small methods (rule of thumb: ≤ 20 lines). Flat nesting: early returns, no `else` after `return`.
- Separate pure logic from I/O. Logic classes (validation, parsing, rendering of a given model, calculations) do not
  access `$_GET`, `$_POST`, `$_SESSION`, `$_SERVER`, `$_COOKIE`, the file system, the database, the network or the
  clock directly. They get their input as arguments and are unit tested.
- Dependencies are passed in through the constructor. No new static state, singletons or global functions. Existing
  static accessors are replaced by explicit dependencies when their code is changed.
- If time matters, inject a `Clock` interface instead of calling `time()` or `new DateTimeImmutable()` in logic.
- Prefer composition over inheritance. Abstract base classes only for a real "is a" relation; interfaces for
  extension points that projects implement (e.g. renderers, rules, template tags).
- Keep good existing patterns. New abstractions need a reason.

## 2. File header

Every PHP file starts with the copyright header followed by `declare(strict_types=1);`, each separated by a blank
line (PER Coding Style):

```php
<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);
```

`@license` names the license of the project (`MIT` for public libraries, `proprietary` for closed projects).
PHP-CS-Fixer adds and fixes the header (see [tooling.md](tooling.md)).

Code adapted from a third-party library keeps that library's license:

- `@license` names its SPDX identifier exactly as the upstream declares it in its `composer.json` or `LICENSE`
  (`LGPL-2.1-only`, `Apache-2.0`); never assume "-or-later".
- A second docblock after `declare(strict_types=1);` names the source (URL), keeps the original `@author` and
  `@copyright` lines unchanged and states that and how the file was changed (required by Apache-2.0 §4b, LGPL §2a).
- The folder of the adapted code contains the full upstream license text of the exact version as `LICENSE`, and the
  upstream `NOTICE` if there is one (Apache-2.0 §4d). A project may add its own `NOTICE` describing its changes.
- `composer.json` `license` is an SPDX expression of all licenses in the package (`MIT AND LGPL-2.1-only AND
  Apache-2.0`). `README.md`, section "License", says which parts are under which license and whether proprietary
  projects may use the package.
- Copyleft code (GPL, LGPL) is never relicensed as MIT. GPL code is never adopted into MIT or proprietary projects.

```php
<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   Apache-2.0
 */

declare(strict_types=1);

/**
 * Adapted from example/library (https://github.com/example/library), see LICENSE and NOTICE in this folder.
 * Changed by Actra AG: reduced to parsing and formatting, adapted to the Actra coding standard.
 *
 * @author    Jane Doe <jane@example.com>
 * @copyright 2020 Jane Doe
 */
```

## 3. Types

- `final` classes by default. `readonly` classes or properties for value objects. Non-final only for intended
  extension points.
- Fully typed properties, parameters, constants and return types. No `mixed` in own code. PHPDoc only for what PHP
  cannot express (`list<FormField>`, `array<string, string>`, `non-empty-string`).
- No untyped "options" arrays. Use small readonly value objects or named arguments instead.
- Nullable types are written as `?Type`.
- **Enums first.** Every fixed set of values (states, types, modes, results, HTML attribute values) is a backed enum,
  never string/int constants or magic strings. Behaviour of a value lives on the enum and uses `match`.
- Narrow external `mixed` (request data, DB rows, JSON, session, config) right at the boundary, with explicit checks
  in one place, and throw a meaningful exception on invalid data (see [security.md](security.md), section 2).
- Prefer immutable objects (`readonly`, `with…()` methods returning a modified copy) and `DateTimeImmutable`.
- Use new PHP features where they make code clearer (pipe operator `|>`, `#[\NoDiscard]` on methods whose result must
  be used, `clone()` with properties, property hooks, asymmetric visibility). Never just to show off.

## 4. Style

- **Named arguments for all calls**, also for PHP functions: `trim(string: $value)`. Exception: methods marked
  `@no-named-arguments` (e.g. PHPUnit's `assert*()`) and variadic calls are called with positional arguments.
- Refer to the own class by its name (`FormField::create()`), not `self::` / `static::`. `static::` only where late
  static binding is intended.
- `match` instead of `switch`. No `@` error suppression.
- No `eval()`, no variable variables (`$$name`), no `extract()`, no `compact()`, no `global`, no `$GLOBALS`.
- Constructor property promotion for simple constructors.
- Return early instead of nesting; one level of abstraction per method.

## 5. Explicit comparisons

Every comparison says exactly what it checks. Implicit checks hide typos, treat `'0'` as empty and mix up "missing" with
"null".

- **Always `=== null` / `!== null`, never `is_null()`.**
- **Never `isset()`**, in any form (variables, array keys, properties, superglobals):
  - array keys: `array_key_exists(key: 'name', array: $data)`, followed by a type check of the value,
  - nullable values: `$value !== null`,
  - variables and properties: always initialize and type them, so they exist.
- **Never `empty()`**: compare with the concrete empty value: `$value === ''`, `$items === []`, `$count === 0`.
- Strict comparisons only (`===`, `!==`), never `==` / `!=`. `in_array()` and `array_search()` with `strict: true`.
- Conditions are booleans: `if ($items !== [])`, not `if ($items)` or `if (count($items))`.
- No short ternary `?:`. The null coalescing operator `??` only on nullable-typed values (`$name ?? 'unknown'`), never
  to hide undefined array keys or variables.

PHPStan reports violations of these rules (see [tooling.md](tooling.md)).

## 6. Exceptions and errors

- Throw specific SPL exceptions (`InvalidArgumentException`, `LogicException`, `UnexpectedValueException`, …) or own
  exceptions extending them.
- The message tells the developer what is wrong and how to fix it, including the offending value where safe (never
  passwords, tokens or personal data).
- Do not catch exceptions just to ignore them. Catch only where you can handle the error, or at the application
  boundary to log it and show a generic error page.
- Never show stack traces, SQL or internal messages to users in production.

## 7. Comments

- Comments explain *why*, not *what*. Keep them short.
- Class PHPDoc: one or two sentences about the purpose, when the name alone is not enough.
- No PHPDoc that repeats the signature (`@param string $name The name`).
- No dead code, no commented-out code, no `TODO` without a linked task (issue or `docs/plans/<topic>/plan.md`).
