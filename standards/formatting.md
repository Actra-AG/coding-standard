# Formatting

## 1. Base standards

All PHP code follows these PHP-FIG standards:

| Standard                                                          | Topic                                                     |
|:------------------------------------------------------------------|:----------------------------------------------------------|
| [PSR-1](https://www.php-fig.org/psr/psr-1/)                       | Basic coding standard (tags, side effects, naming basics) |
| [PER Coding Style 3.1](https://www.php-fig.org/per/coding-style/) | Code style, successor of PSR-12 and stricter than it      |
| [PSR-4](https://www.php-fig.org/psr/psr-4/)                       | Autoloading: namespace ↔ directory mapping                |

Where an interface standard exists and fits, implement it instead of inventing an own one, e.g.
[PSR-3](https://www.php-fig.org/psr/psr-3/) (logger), [PSR-7](https://www.php-fig.org/psr/psr-7/) /
[PSR-15](https://www.php-fig.org/psr/psr-15/) (HTTP), [PSR-20](https://www.php-fig.org/psr/psr-20/) (clock). Projects
with a zero-dependency policy may define equivalent own interfaces.

Always the latest version of PER Coding Style applies. The rules below add to or clarify it; there are no deviations.
They are enforced with PHP-CS-Fixer and the shared rule set of this package (see [tooling.md](tooling.md)). Rules that
the tool cannot check yet are checked in the review.

## 2. Spacing and indentation

- Indentation: 4 spaces, no tabs.
- Soft line limit: 120 characters. Break longer statements, argument lists and conditions over multiple lines, one item
  per line, with a trailing comma in multi-line argument lists, parameter lists and arrays.
- One blank line between methods and between logical blocks inside a method. No multiple blank lines in a row.
- One blank line between the header blocks: after `<?php`, after the file docblock, after `declare(strict_types=1);`,
  after `namespace` and after the `use` block (see section 3).
- Opening braces of classes and methods on their own line; of control structures on the same line.
- Always use braces for control structures, also for one-line bodies.
- One space around binary operators (`=`, `===`, `.`, `+`, `??`, `=>`, `|>`) and after a cast: `(int) $value`.
- No space after `!`: `!$isValid`.
- Short array syntax `[]` only.
- Single quotes for strings without interpolation or escape sequences.

```php
public function createUser(
    string $emailAddress,
    UserRoleEnum $role,
    ?DateTimeImmutable $validUntil = null,
): User {
    if (!$this->emailValidator->isValid(emailAddress: $emailAddress)) {
        throw new InvalidArgumentException(message: 'Invalid email address: ' . $emailAddress);
    }

    return new User(emailAddress: $emailAddress, role: $role, validUntil: $validUntil);
}
```

## 3. File structure

Every PHP file has this order, separated by one blank line each (PER Coding Style, section 3):

1. `<?php` opening tag
2. File docblock with the copyright header (see [php.md](php.md), section 2)
3. `declare(strict_types=1);`
4. `namespace`
5. `use` imports (classes, then functions, then constants), sorted alphabetically, no unused imports, no grouped imports
6. One class, interface, trait or enum

Files containing only PHP omit the closing `?>` tag. Files declare symbols or cause side effects, never both (PSR-1).

## 4. Files and encoding

- UTF-8 without BOM, Unix line endings (`LF`).
- No trailing whitespace.
- Every file ends with a single newline (`LF`), for all file types.
- File names: a PHP class file is named exactly like the class (`UserRepository.php`). Other files use lowercase with
  hyphens (`code-quality.md`, `plan.md`), except well-known names (`README.md`, `AGENTS.md`, `UPGRADE.md`).
- Every project has an `.editorconfig` based on the [template](../templates/.editorconfig).
