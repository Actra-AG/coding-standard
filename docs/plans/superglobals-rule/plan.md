# Plan: optional PHPStan rule against superglobals

Follow-up noted while moving `actra/yuf` to request and session objects (yuf v4.29.0 `HttpRequest`, v4.30.0
`Session`).

## Goal

An opt-in PHPStan configuration that reports direct access to `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_FILES`,
`$_SERVER`, `$_SESSION` and `$GLOBALS`, for projects whose framework has request and session objects.

## Why opt-in, not in `config/phpstan.neon`

- Projects on a framework with its own request/session components (yuf, Craft CMS / Yii, Symfony) should use those
  components; the rule is valid there (the framework code itself is in `vendor/` and not analysed).
- WordPress plugins read `$_POST` / `$_GET` directly by design (together with WordPress' sanitizing functions); legacy
  code and plain entry points (`index.php`) need the superglobals as well. A default rule would force large ignore lists
  there.

## Tasks

1. Check how `spaze/phpstan-disallowed-calls` covers superglobals (`disallowedSuperglobals`) and which error
   identifiers it reports.
2. Add `config/phpstan-no-superglobals.neon` with the rule and a message that names the replacement ("use the request
   or session object of your framework").
3. Document it in `standards/security.md` / `standards/tooling.md`: when to include it, how to allow single files
   (entry points, the framework's own request factory) with `allowIn` paths instead of baseline entries.
4. `UPGRADE.md` section (no ⚠️: opt-in, no change for existing projects); README.
5. Try it in `actra/yuf` after v4.30.0: expected allowed places are `HttpRequest::fromGlobals()`, `Core`
   (`DOCUMENT_ROOT`) and the native session storage / session handler.

## Handover notes

### 2026-10-08: tasks 1–4 done

- `disallowedSuperglobals` entries are checked one by one; an entry is skipped only if its own `allowIn` matches. An
  additional entry with `allowIn` in the project does therefore not allow anything: the allowed paths must be part of
  our entry. `config/phpstan-no-superglobals.neon` reads them from the parameter `actraSuperglobalsAllowIn` (declared
  in `parametersSchema`, default `[]`). Default error identifier: `disallowed.variable` (no own `errorIdentifier`,
  like `disallowed.neon`).
- `$GLOBALS` and `$_REQUEST` are not part of the new entry: `disallowed.neon` forbids them in every project, without
  `allowIn`. `$_ENV` is not covered (not in the goal list).
- Verified with PHPStan in Docker (PHP 8.5.11) in a scratch project: superglobals reported in `src/`, file listed in
  `actraSuperglobalsAllowIn` allowed, `$_REQUEST` still reported there; without the parameter everything is reported.
- Documented in `standards/tooling.md` (section 3), `standards/security.md` (section 2), `UPGRADE.md` (v1.3.0), README.
- Open: task 5 (try it in `actra/yuf`).

### 2026-10-08: task 5 done, plan complete

- `actra/yuf` switched the rule on in commit 44914ac (`build: switch on the PHPStan rule against superglobals`) and
  uses it with coding standard v1.5.0. `actraSuperglobalsAllowIn` lists the expected places (`Core`, `HttpRequest`,
  `NativeSessionStorage`, `AbstractSessionHandler`) and the tests that prepare or inspect superglobals; the baseline has
  no `disallowed.variable` entries.
- `ddev exec vendor/bin/phpstan analyse` is green. A probe file outside the allowed paths reports `$_GET` and
  `$_SESSION` as `disallowed.variable` with the message from the rule.
- No change to the rule needed.
