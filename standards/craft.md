# Craft CMS

For projects built on Craft CMS (Craft 5, Yii 2). Each section refines one general rule for Craft; everything else
in the standard applies unchanged.

## 1. Dependencies

Refines [php.md](php.md), section 1 (dependencies through the constructor).

- Glue code (module and plugin classes, controllers, console commands, event handlers, queue jobs, migrations)
  reaches Craft and the project's own services through the service locator: `Craft::$app->getElements()`,
  `Module::getInstance()->getOrders()`. Do not autowire Craft or module components through the Yii container and do
  not use controller action injection: one style per layer.
- Logic classes owned by the project never use `Craft::$app`, `getInstance()` or other static accessors. They receive
  their dependencies through the constructor (a `Clock`, settings values, a lookup `Closure`) or as method arguments;
  the glue code creates them with `new`. They are unit tested without a Craft bootstrap or a database.

Reason: Craft core and its documentation use the service locator. Yii's container autowiring creates a second
instance of a service that is registered as a component, and many objects never pass through a project constructor
(serialized queue jobs, elements, fields, migrations, static `Event::on()` handlers). Logic free of the locator meets
the goal of the general rule: explicit dependencies, testable logic.

## 2. Templates

Refines [AGENTS.md](../AGENTS.md), "Working on a task", and [html-javascript.md](html-javascript.md), which allow AI
assistants template changes only as long as the design does not change.

- AI assistants may also move logic (calculations, lookups, decisions, `{% set %}` blocks that compute) out of Twig
  templates into PHP and replace it with one call to a named PHP entry point (Twig function or variable). The logic
  goes into a logic class (section 1) with unit tests.
- The rendered HTML stays identical, proven by diffing every page that renders the template before and after the
  change: identical once every run of whitespace is collapsed to one space, because removing a `{% set %}` or
  `{% for %}` changes the indentation Twig emits around it. Mail templates are compared byte for byte, because
  Markdown is whitespace sensitive. If the HTML cannot stay identical, it is a task for the frontend developer.
- The change is listed under "Frontend review" with the note "logic moved to PHP, HTML identical".

Reason: logic in Twig cannot be type checked, tested or followed in the IDE. The backend owns that logic; the diff
proof leaves the markup untouched.

## 3. Database

Refines [naming.md](naming.md), section 4 (snake_case columns, `created_at`).

- Tables follow [naming.md](naming.md), section 4 (snake_case, plural) and are written with the prefix syntax
  `{{%table}}` in migrations and queries.
- Columns follow Craft's convention: camelCase (`entryId`, `siteId`, `isActive`), with `dateCreated`, `dateUpdated`
  and `uid` as Craft creates them (`dateDeleted` for soft deletes). The other rules of section 4 apply to the
  camelCase name.

Reason: Craft's ActiveRecord fills `dateCreated`, `dateUpdated` and `uid` itself, and every Craft table uses camelCase
columns; snake_case would lose that and mix two styles in one query.

## 4. Tooling

- Craft projects include [phpstan-no-superglobals.neon](../config/phpstan-no-superglobals.neon) (see
  [tooling.md](tooling.md), section 3), with the web entry point `web/index.php` in `actraSuperglobalsAllowIn`.
- `scanDirectories` for the classes Craft generates (`storage/runtime/compiled_classes`, the `CustomFieldBehavior`)
  is allowed: it is generated code, not own or Actra classes (see [tooling.md](tooling.md), section 7).
