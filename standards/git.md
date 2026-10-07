# Git

## 1. Commit messages

[Conventional Commits](https://www.conventionalcommits.org) with a bullet list body:

```
type(scope): summary in imperative mood, lowercase, no period

- First change
- Second change
- Document the changes in README.md and UPGRADE.md

Attention: What users of the code must know or do, if anything.
```

- **type:** `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `style`, `build`, `ci`, `chore`
- **scope:** the affected area or module (`form`, `session`, `db`), optional
- `!` after the scope for breaking changes: `feat(form)!: require a label for all fields`
- The empty line after the subject is required, otherwise Git treats the whole message as subject.
- The body lists the actual changes as `- ` bullets, in imperative mood.
- The optional `Attention:` paragraph explains what consumers must adapt or watch out for.
- Subject line up to 72 characters if possible.
- One topic per commit. Formatting changes of unrelated code go into their own `style` commit.
- Commit messages never contain secrets, customer names or personal data.

## 2. Branches and pull requests

- `main` is always releasable and green (`composer check`).
- Work on feature branches named `type/short-description` (`feat/form-labels`, `fix/csrf-compare`) when working with
  pull requests.
- A pull request covers one topic and describes what changed and why.

## 3. Ignored and exported files

- Prefer a **whitelist** `.gitignore`: ignore everything (`*`) and allow the tracked files and directories explicitly.
  This prevents accidental commits of secrets, local configs and generated files. New top-level files or directories
  must be added to the whitelist.
- Never commit: `vendor/`, `node_modules/`, caches (including `.php-cs-fixer.cache`), logs, `.env*` files except
  `.env.example*`, IDE settings, local DDEV overrides.
- Libraries mark development files in `.gitattributes` as `export-ignore` (`tests/`, `docs/`, `.ddev/`, `phpstan.neon`,
  `.php-cs-fixer.dist.php`, `phpunit.xml`, `AGENTS.md`, …), so they are not part of the installed package.

## 4. Review before commit

Before committing, check:

1. Is `README.md` still correct?
2. Does `UPGRADE.md` need an entry (always for breaking changes in libraries)?
3. Is `composer check` green?
4. Does the commit message follow section 1?
5. For a release: which version tag follows (see [versioning.md](versioning.md))?
