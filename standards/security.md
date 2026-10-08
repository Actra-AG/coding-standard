# Security

Base reference: [OWASP Top 10](https://owasp.org/www-project-top-ten/) and the
[OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/). Keep existing security features working and covered
by tests.

## 1. Principles

- **Never trust input.** Everything from outside the code is untrusted: `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`,
  `$_SERVER` (including `HTTP_HOST`, `REMOTE_ADDR` behind proxies, headers), path variables, uploaded files, API
  responses, data from the database that was entered by users, environment-specific config.
- **Validate input, escape output.** Validation happens at the boundary when data enters; escaping happens at the
  moment data leaves into another context (HTML, SQL, shell, URL, header, mail). One does not replace the other.
- **Secure by default.** The safe variant is the default and the short one; the unsafe one is explicit and named so
  (`HtmlText::unencoded()`).
- **Fail closed.** On missing or invalid data, permissions or tokens, deny and stop; never fall back to a permissive
  default.

## 2. Input: sanitizing and validation

Order: **read → sanitize (normalize) → validate → use typed values.**

- Read input in one place per request (request/controller layer) and turn it into typed values or value objects.
  Logic classes never read superglobals (see [php.md](php.md), section 1).
- If the framework has request and session objects, use them instead of `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`,
  `$_SERVER` and `$_SESSION`; the opt-in PHPStan configuration `phpstan-no-superglobals.neon` enforces this (see
  [tooling.md](tooling.md), section 3).
- **Sanitizing** only normalizes harmless representation differences, e.g. trimming whitespace, unifying line breaks,
  Unicode normalization, removing thousands separators, lowercasing a domain. It never "repairs" malicious input
  (stripping tags, removing quotes) and is never a replacement for validation or escaping.
- **Validation** checks the sanitized value against an explicit whitelist of what is allowed: type, length, range,
  format, allowed values (enums), and business rules. Reject invalid input with a clear message to the user; do not
  silently change it.
- Use `filter_var()` with explicit filters and `FILTER_NULL_ON_FAILURE` or dedicated validators. No hand-made regular
  expressions for email addresses, URLs or IP addresses when a tested validator exists.
- Validate on the server, always. Client-side validation (HTML attributes, JavaScript) is only a convenience.
- Validate identifiers that come from input (sort columns, table names, file names, redirect targets) against a
  whitelist or an enum, never against a blacklist.
- Redirects: only to relative paths or whitelisted hosts (no open redirects).
- Mass assignment: map input fields explicitly to properties; never pass `$_POST` directly to a model or query.

## 3. Output escaping

- All output is HTML-escaped by default: `htmlspecialchars(string: $value, flags: ENT_QUOTES | ENT_SUBSTITUTE,
  encoding: 'UTF-8')` or the framework helper that does this. Unescaped output must be explicit and only for HTML
  that the application generated itself.
- Escape for the target context: HTML text and attributes (`htmlspecialchars`), URLs (`rawurlencode()` for path parts,
  `http_build_query()` for query strings), JavaScript (`json_encode()` with `JSON_HEX_TAG | JSON_HEX_AMP |
  JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR`), shell (`escapeshellarg()`, better: no shell), CSV (prefix
  cells starting with `=`, `+`, `-`, `@` against formula injection).
- Never put user input into `<script>`, `style` attributes, event handler attributes or unquoted attributes.
- Template engines escape by default; disabling escaping is reviewed like unescaped output.

## 4. SQL

- SQL only with bound parameters (prepared statements). No string concatenation or interpolation of values.
- Identifiers that cannot be bound (column, table, sort direction) are validated against a whitelist or an enum.
- `LIKE` patterns: escape `%`, `_` and the escape character in the search term and bind it as parameter.
- Use the least privileged database user the application needs.

## 5. CSRF

- Every state-changing request (`POST`, `PUT`, `PATCH`, `DELETE`) is protected by a CSRF token. `GET` requests never
  change state.
- Tokens are generated with `random_bytes()` (at least 32 bytes), stored in the session and sent as hidden form field
  or request header.
- Tokens are compared with `hash_equals()`, never with `===`.
- An invalid or missing token aborts the request (fail closed) with a clear, non-technical error message.
- Session cookies use `SameSite=Strict` or `Lax` as additional defense, not as replacement for the token.

## 6. Sessions and cookies

- Session cookie flags: `Secure`, `HttpOnly`, `SameSite=Strict` (`Lax` only where cross-site navigation requires it),
  `session.use_strict_mode=1`, `session.use_only_cookies=1`.
- Regenerate the session ID (`session_regenerate_id(delete_old_session: true)`) on login, logout and privilege change.
- On logout, remove all user data from the session.
- Never put session IDs or tokens into URLs, logs or error messages.

## 7. Authentication and passwords

- Passwords are hashed with `password_hash()` (`PASSWORD_ARGON2ID`, or `PASSWORD_DEFAULT`) and checked with
  `password_verify()`; rehash with `password_needs_rehash()` after login. Never use plain or salted `md5`/`sha*`
  hashes for passwords.
- Random values for security (tokens, IDs, nonces, salts): `random_bytes()` / `random_int()` only, never `rand()`,
  `mt_rand()`, `uniqid()` or `openssl_random_pseudo_bytes()`.
- Compare secrets (tokens, signatures, API keys) with `hash_equals()`.
- Rate-limit or slow down login attempts. Error messages do not reveal whether the user name or the password was wrong.
- Check authorization on the server for every request and every object accessed (no "hidden" URLs or IDs as
  protection).

## 8. HTTP headers and CSP

- Send a Content Security Policy without `unsafe-inline` and `unsafe-eval`; inline scripts and styles only with a
  per-request nonce.
- Send `X-Content-Type-Options: nosniff`, `Referrer-Policy`, a `frame-ancestors` CSP directive (or `X-Frame-Options`),
  and `Strict-Transport-Security` on HTTPS.
- Serve everything over HTTPS.

## 9. Files and uploads

- Never use user input directly in file paths. Resolve the path and check that it is inside the allowed directory
  (`realpath()` + prefix check).
- Uploads: check size and the real MIME type (`finfo`), generate own file names, store outside the document root or
  without execution rights, and deliver them with `Content-Disposition` and the checked content type.
- No `include`/`require` of paths built from input. No `unserialize()` (use JSON).

## 10. Secrets and sensitive data

- No secrets, credentials, API keys, private hostnames, IP addresses, personal data or real customer data in the
  repository, in tests, fixtures, examples, issues or commit messages. Use example values (`example.com`, RFC 5737
  IP ranges such as `192.0.2.0/24`).
- Secrets live in untracked environment files (e.g. `.env.php`, listed in `.gitignore`); the repository contains only
  an example file with placeholders (`.env.example.php`).
- Logs never contain passwords, tokens, session IDs or full payment data. Mask personal data where possible.
- A secret that was committed once is compromised: rotate it, removing it from the history is not enough.
- Keep dependencies up to date; check with `composer audit`.
