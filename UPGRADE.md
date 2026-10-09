# Upgrade

Changes of the Actra coding standard, newest first. ⚠️ marks changes that may make `composer check` of existing
projects fail or that change how projects work.

## v1.9.0 (2026-10-09)

- Short `README.md` with details in `docs/`, short `UPGRADE.md` entries; older sections may move to
  `docs/upgrade/v<major>.md` ([AGENTS.md](AGENTS.md), "Files"; [versioning.md](standards/versioning.md), section 6).
- Libraries ship `docs/`: replace `/docs/ export-ignore` in `.gitattributes` with `/docs/*/plan.md export-ignore`
  ([git.md](standards/git.md), section 3).

## v1.8.0 (2026-10-09)

- Commit type `content` for editorial changes; SEO is a scope, not a type. Body optional for self-explanatory changes
  ([git.md](standards/git.md), section 1).
- Answers show only changed code blocks with file path ([AGENTS.md](AGENTS.md), "Response style").
- Projects remove these rules and own commit types (e.g. `seo`) from their `AGENTS.md`.

## v1.7.0 (2026-10-08)

- ⚠️ Libraries require dependencies with breaking changes in minor versions with `~X.Y.Z` instead of `^X.Y`;
  applications keep `^` ([versioning.md](standards/versioning.md), section 8). Before: `"actra/yuf": "^4.37"`. After:
  `"actra/yuf": "~4.37.0"`.

## v1.6.0 (2026-10-08)

- Plain CSS, no external JavaScript library without a reason ([html-javascript.md](standards/html-javascript.md)).
  Projects with a CSS preprocessor or JavaScript libraries name the reason in their `AGENTS.md`.
- Rules for raising dependencies ([versioning.md](standards/versioning.md), section 8).

## v1.5.0 (2026-10-08)

- Rules that apply to every Actra project go into the coding standard ([AGENTS.md](AGENTS.md), "Working on a task").

## v1.4.1 (2026-10-08)

- Fix the example API in [security.md](standards/security.md), section 1.

## v1.4.0 (2026-10-08)

- New [i18n.md](standards/i18n.md). Existing hard-coded texts are moved when their code is changed.
- Libraries ship database update scripts and note required asset rebuilds ([versioning.md](standards/versioning.md),
  section 7), and check views in a consuming project ([testing.md](standards/testing.md)).

## v1.3.0 (2026-10-08)

- Opt-in [phpstan-no-superglobals.neon](config/phpstan-no-superglobals.neon) for projects with request and session
  objects ([tooling.md](standards/tooling.md), section 3).

## v1.2.0 (2026-10-07)

- ⚠️ Settings bundles end with `Settings`, without `Model` suffix ([naming.md](standards/naming.md)). Before:
  `SessionSettingsModel`. After: `SessionSettings`. Rename when the code is changed; public libraries keep the old name
  as deprecated alias for one release.
- External interfaces (`LoggerInterface`) keep their names.

## v1.1.1 (2026-10-07)

- PHP-CS-Fixer rules that broke named arguments are disabled (`strict_param`, `strict_comparison`,
  `modernize_types_casting`, `pow_to_exponentiation`, `random_api_migration`). Projects that ran `composer cs:fix`
  with v1.1.0 or older check the changed calls and comparisons ([tooling.md](standards/tooling.md), section 4).

## v1.1.0 (2026-10-07)

- ⚠️ Breaking changes are released as minor versions; deprecated code may be removed in any following minor version.
  Read `UPGRADE.md` of dependencies before `composer update` ([versioning.md](standards/versioning.md)).
- ⚠️ `UPGRADE.md` sections have a release date instead of "unreleased".
- ⚠️ Linear Git history, no merge commits ([git.md](standards/git.md)).
- Prefer short development cycles.

## v1.0.0 (2026-10-07)

First release: global `AGENTS.md`, standards, shared PHPStan and PHP-CS-Fixer configuration, templates.
