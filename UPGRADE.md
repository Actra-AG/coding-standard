# Upgrade

Changes of the Actra coding standard, newest first. ⚠️ marks changes that may make `composer check` of existing projects
fail or that change how projects work.

## v1.1.0 (2026-10-07)

### ⚠️ Breaking changes no longer require a major version

Breaking changes are released as minor versions; a major version marks a major step with many new features. Deprecated
code may be removed in any following minor version. Projects read `UPGRADE.md` of their dependencies before
`composer update` (see [versioning.md](standards/versioning.md)).

### ⚠️ `UPGRADE.md` sections have a release date instead of "unreleased"

The commit that releases a version adds its section with version and date. Projects without `UPGRADE.md` decide with
every change whether adding one would be useful.

### ⚠️ Linear Git history

No merge commits: rebase branches onto `main` and integrate them with fast-forward or "Rebase and merge" (see
[git.md](standards/git.md)).

### Short development cycles

Prefer small changes that are released soon.

## v1.0.0 (2026-10-07)

First release: global `AGENTS.md`, standards, shared PHPStan and PHP-CS-Fixer configuration, templates.
