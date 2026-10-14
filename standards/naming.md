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
| View class (yuf)              | Exactly the file title, lowerCamelCase    | `hosts`, `domainDelete`                      |
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
| Database tables and columns   | snake_case, see section 4                 | `user_logins`, `created_at`                  |
| HTML/CSS classes, `data-*`    | kebab-case                                | `form-field`, `data-confirm-message`         |

Interfaces and traits have no suffix, so the name used in type hints names the role (`Clock $clock`); the
implementations get specific names (`SystemClock`, `FixedClock`). External interfaces such as the PSR interfaces
(`LoggerInterface`, `ClockInterface`) keep their names: implement them directly, never wrap or alias them only to drop
the suffix.

View classes of yuf projects (and actra/backend) are a framework convention: the framework maps the requested file
title to the view class name (`hosts.html` → class `hosts`, `domainDelete-12.html` → class `domainDelete`), so view
classes in `view/<group>/php/` are named exactly like the file title, not PascalCase.

## 3. Methods

- Getters return a value and have no side effects: `getName()`, boolean getters `isActive()` / `hasRole()`.
- Methods that create a new instance start with `create`, `from` or `with` (for modified immutable copies:
  `withAmount()`).
- Methods returning `null` for "not found" are named accordingly (`findById(): ?User`); methods that throw instead are
  named `getById(): User`.
- Do not name methods after the implementation (`loopUsers()`), but after the purpose (`activeUsers()`).

## 4. Database

- Tables: snake_case, plural (`products`, `orders`, `user_logins`).
- Link tables (pure n:m relations): singular, both table names in alphabetical order (`customer_user`,
  `customer_registrar`). The primary key may be the combined key, no separate `id` column.
- Columns: snake_case, singular (`price`, `billing_address`).
- Primary key: `id`. A stable business key may be the primary key instead (`customer_number`, the `name` of a
  registrar or TLD); then no additional `id` is needed.
- Foreign keys: singular name of the referenced table + `_id` (`user_id`). For a business key: singular table name +
  key name (`registrar_name`), or only the key name if it already contains the table name (`customer_number`).
- Timestamps: `created_at`, `updated_at`, `deleted_at`.
- Booleans read as a statement: `is_active`, `has_api`.
- Craft projects use camelCase columns (see [craft.md](craft.md), section 3).

## 5. Legacy names

Existing names that break these rules (e.g. snake_case methods, enums without `Enum` suffix) are renamed when their code
is changed. In public libraries this is a breaking change (see [versioning.md](versioning.md)).
