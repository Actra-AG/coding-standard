# Testing

## 1. Layout

```
tests/
  bootstrap.php   # sets up autoloading for src/ and tests/
  Unit/           # pure logic, no I/O, fast
  Integration/    # optional: code that needs a real database, file system or HTTP
  Double/         # hand-written test doubles (FixedClock, InMemorySession, …)
phpunit.xml
```

- Namespace and directory of a test mirror the tested code: `<vendor>\<package>\tests\Unit\datacheck\…` in
  `tests/Unit/datacheck/` tests `<vendor>\<package>\datacheck\…`. Applications without vendor namespace use
  `tests\Unit\…`: `tests\Unit\model\…` tests `app\model\…`.
- PHPUnit (current major version). Data providers and other metadata via attributes (`#[DataProvider]`, `#[Test]` is
  not needed with the `test` prefix), no annotations.
- Test classes are `final`. Data providers are `public static` and return `iterable` with named cases.

## 2. What to test

- **Unit tests are mandatory for every new or refactored logic class:** validation rules, sanitizers, parsers, template
  tags, renderers (given model → expected HTML), value objects, enums with behaviour, security helpers (CSRF token,
  escaping, password hashing).
- **Before refactoring existing code**, add characterization tests that capture its current behaviour and output, so
  lost features show up as failing tests.
- Every bug fix comes with a test that fails without the fix.
- Test the edge cases: empty string, `'0'`, whitespace, maximum length, Unicode, negative numbers, overflows, invalid
  types from external input.
- Code that needs a real database or HTTP is kept thin and tested via its pure parts; integration tests are optional
  and must not depend on external services or real data.
- A library with its own example app checks changes of routing, views, templates, generated HTML and assets in that
  app in the browser. Libraries without one check them in a consuming project that uses the library checkout as
  Composer path repository (`"type": "path"`). The project's `AGENTS.md` names the app and its URL.
- Every project has a strict-types guard test: PHP-CS-Fixer only checks its configured paths, so a unit test scans
  every directory with PHP files (application code, tests, entry points in `public/`, scripts in `local/` or `bin/`,
  config) and fails for any file without `declare(strict_types=1);`. Only dependencies and generated code (caches)
  are excluded:
  ```php
  final class StrictTypesTest extends TestCase
  {
      // Paths relative to the project root without own PHP code or with generated code
      private const array EXCLUDED = ['.ddev', '.git', 'node_modules', 'var/cache', 'vendor'];

      public function testEveryPhpFileDeclaresStrictTypes(): void
      {
          $root = dirname(path: __DIR__, levels: 2);
          $files = new RecursiveIteratorIterator(
              iterator: new RecursiveCallbackFilterIterator(
                  iterator: new RecursiveDirectoryIterator(directory: $root, flags: FilesystemIterator::SKIP_DOTS),
                  callback: static fn(SplFileInfo $file): bool => !in_array(
                      needle: substr(string: $file->getPathname(), offset: strlen(string: $root) + 1),
                      haystack: self::EXCLUDED,
                      strict: true,
                  ),
              ),
          );
          $missing = [];
          foreach ($files as $file) {
              if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                  continue;
              }
              $code = file_get_contents(filename: $file->getPathname());
              if ($code === false || preg_match(pattern: '/^declare\(strict_types=1\);$/m', subject: $code) !== 1) {
                  $missing[] = $file->getPathname();
              }
          }

          self::assertSame([], $missing);
      }
  }
  ```

## 3. How to write tests

- Test names describe behaviour: `testRequiredRuleFailsForEmptyString()`.
- One assertion topic per test. Use data providers for tables of cases.
- Arrange – act – assert, separated by a blank line.
- Tests are deterministic: no real clock (inject a `FixedClock`), no random values without fixed seed, no network,
  no dependency on execution order or on other tests.
- Test doubles are small hand-written classes in `tests/Double/`, implementing the same interface as the production
  class. Prefer them over mocking frameworks.
- Test data uses example values only (`example.com`, `192.0.2.1`, invented names); never real customer data.
- Tests follow the same coding standard as production code (types, PHPStan level, header).

## 4. Definition of Done (every task)

1. `composer check` is green (code style, static analysis without new baseline entries, all tests pass).
2. New and refactored logic classes have unit tests; bug fixes have a regression test.
3. `UPGRADE.md` lists every breaking change (libraries; projects without one decide whether to add it); `README.md`
   is updated where needed. Both stay short (see [versioning.md](versioning.md), section 6, and
   [AGENTS.md](../AGENTS.md), "Files").
4. Handover notes are written in the plan (`docs/plans/<topic>/plan.md`), if the task belongs to one.
5. Frontend changes in projects with a frontend developer or designer: the "Frontend review" list is written (see
   [AGENTS.md](../AGENTS.md), "Working on a task").
