# Naming

## 1. General

- Names say what something is or does. No abbreviations (`$formField`, not `$ff`; `$request`, not `$req`). Common
  acronyms are fine (`$url`, `$id`, `$csrfToken`, `$html`).
- Acronyms are written like normal words in camelCase and PascalCase: `HtmlText`, `JsonUtils`, `$userId`, `CsrfToken`.
- English only, in code and in comments. User-facing texts may be in other languages.
- Booleans read as a statement: `$isValid`, `$hasAccess`, `$canEdit`, `isRequired()`.
- Collections are plural (`$users`) or explicit (`$userCollection`); single items are singular.

## 2. Elements

| Element                       | Convention                                | Example                                      |
|:------------------------------|:------------------------------------------|:---------------------------------------------|
| Vendor namespace              | lowercase                                 | `actra\`                                     |
| Package / sub namespace       | camelCase, matching the directory (PSR-4) | `actra\yuf\datacheck\sanitizerTypes`         |
| Class, interface, trait       | PascalCase, noun                          | `UserRepository`, `Clock`                    |
| Abstract class                | `Abstract` prefix                         | `AbstractSessionHandler`                     |
| Interface                     | No `Interface` suffix, names the role     | `Clock`, `CsrfTokenSource`                   |
| Trait                         | No `Trait` suffix, names the ability      | `HasTimestamps`                              |
| Enum                          | PascalCase with `Enum` suffix             | `RequestMethodEnum`                          |
| Enum case                     | UPPER_SNAKE_CASE                          | `RequestMethodEnum::GET`, `STATUS_ACTIVE`    |
| Exception                     | `Exception` suffix                        | `DbRowValueException`                        |
| Value object                  | Noun, no type suffix                      | `TimeOfDay`, `EmailAddress`                  |
| Settings bundle               | `Settings` suffix, no `Model` suffix      | `SessionSettings`, `DbSettings`              |
| Class constant                | UPPER_SNAKE_CASE, typed                   | `private const string HASH_ALGORITHM`        |
| Method, function              | camelCase, verb first                     | `createFromSqlQuery()`, `isValid()`          |
| Property, variable, parameter | camelCase                                 | `$emailAddress`                              |
| Named constructor             | `create…()` / `from…()`                   | `FormField::create()`, `Money::fromString()` |
| Test class                    | Class under test + `Test`                 | `AmountParserTest`                           |
| Test method                   | `test` + described behaviour              | `testRequiredRuleFailsForEmptyString()`      |
| Data provider                 | Topic + `Provider`, `public static`       | `invalidAmountProvider()`                    |
| Database tables and columns   | snake_case                                | `user_login`, `created_at`                   |
| HTML/CSS classes, `data-*`    | kebab-case                                | `form-field`, `data-confirm-message`         |

Interfaces and traits have no suffix, so the name used in type hints names the role (`Clock $clock`); the
implementations get specific names (`SystemClock`, `FixedClock`). External interfaces such as the PSR interfaces
(`LoggerInterface`, `ClockInterface`) keep their names: implement them directly, never wrap or alias them only to drop
the suffix.

## 3. Methods

- Getters return a value and have no side effects: `getName()`, boolean getters `isActive()` / `hasRole()`.
- Methods that create a new instance start with `create`, `from` or `with` (for modified immutable copies:
  `withAmount()`).
- Methods returning `null` for "not found" are named accordingly (`findById(): ?User`); methods that throw instead are
  named `getById(): User`.
- Do not name methods after the implementation (`loopUsers()`), but after the purpose (`activeUsers()`).

## 4. Legacy names

Existing names that break these rules (e.g. snake_case methods, enums without `Enum` suffix) are renamed when their code
is changed. In public libraries this is a breaking change (see [versioning.md](versioning.md)).
