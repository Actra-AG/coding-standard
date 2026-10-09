# Git

## 1. Commit messages

[Conventional Commits](https://www.conventionalcommits.org), short and clean:

```
type(scope): summary in imperative mood, lowercase, no period

- First change
- Second change
- Document the changes in README.md and UPGRADE.md

Attention: What users of the code must know or do, if anything.
```

- **type:**
    - `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `style`, `build`, `ci`, `chore`
    - `content`: editorial changes in websites and CMS projects without code change (texts of pages and blog posts,
      images, downloads, translations of content). `docs` stays for documentation of the project, `feat`/`fix` for
      changed behaviour; mixing content into these types hides real changes and inflates releases.
    - No further project-specific types: a topic like SEO is a scope (`feat(seo): add structured data for events`,
      `content(seo): shorten meta descriptions of the blog`). New types would make histories of projects differ.
- **scope:** the affected area or module (`form`, `session`, `db`, `seo`, `blog`), optional
- `!` after the scope for breaking changes: `feat(form)!: require a label for all fields`
- Subject line up to 72 characters if possible. It says what changes, not which files.
- Body:
    - The empty line after the subject is required, otherwise Git treats the whole message as subject.
    - The body lists the changes as `- ` bullets, in imperative mood, one line per bullet if possible.
    - Omit the body if the subject already says everything (a single, self-explanatory change). Do not repeat the
      subject or list every touched file.
    - The optional `Attention:` paragraph explains what consumers must adapt or watch out for. It is
      required for breaking changes.
- One topic per commit. Formatting changes of unrelated code go into their own `style` commit.
- Commit messages never contain secrets, customer names or personal data.

Examples:

```
content(blog): fix typos in the post about the new office
```

```
content: update the team page

- Add two new team members with photos
- Replace the group photo
```

## 2. Branches and history

- Keep the history linear and clean. **Never create merge commits** that join separate lines of history.
    - Update a branch by rebasing it onto `main` (`git rebase main`), not by merging `main` into it.
    - Integrate a branch with a fast-forward merge (`git merge --ff-only`) after rebasing, or with "Rebase and merge"
      in pull requests. Disable merge commits in the repository settings and require a linear history for `main`.
    - Pull with rebase (`git pull --rebase`, or `git config pull.rebase true`).
- `main` is always releasable and green (`composer check`).
- Prefer short development cycles: short-lived branches with one topic, integrated and released soon (see
  [versioning.md](versioning.md)).
- Work on feature branches named `type/short-description` (`feat/form-labels`, `fix/csrf-compare`) when working with
  pull requests.
- A pull request covers one topic and describes what changed and why.

## 3. Ignored and exported files

- Prefer a **whitelist** `.gitignore`: ignore everything (`*`) and allow the tracked files and directories explicitly.
  This prevents accidental commits of secrets, local configs and generated files. New top-level files or directories
  must be added to the whitelist.
- Never commit: `vendor/`, `node_modules/`, caches (including `.php-cs-fixer.cache`), logs, `.env*` files except
  `.env.example*`, IDE settings, local DDEV overrides.
- Libraries mark development files in `.gitattributes` as `export-ignore` (`/tests/`, `/.ddev/`, `/phpstan.neon`,
  `/.php-cs-fixer.dist.php`, `/phpunit.xml`, `/AGENTS.md`, `/docs/*/plan.md`, …), so they are not part of the
  installed package. `docs/` itself is shipped: it holds the details of `README.md` and `UPGRADE.md` (see
  [versioning.md](versioning.md), section 6).

## 4. Review before commit

Before committing, check:

1. Is `README.md` still correct?
2. Does `UPGRADE.md` need an entry (always for breaking changes in libraries)? If the project has none, would it be
   useful to add one (see [versioning.md](versioning.md))?
3. Is `composer check` green?
4. Does the commit message follow section 1?
5. For a release: which version tag follows, and does `UPGRADE.md` have the section with this version and today's
   date (see [versioning.md](versioning.md))?
