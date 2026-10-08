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
