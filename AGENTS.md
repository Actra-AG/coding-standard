# AGENTS.md

Global instructions for developers and AI assistants working in any Actra project. Projects install this repository as
development dependency `actra/coding-standard`; their own `AGENTS.md` refers to this file and adds project-specific
instructions. Links in this file are relative to this file (`vendor/actra/coding-standard/` in projects).

## Standards

- Follow the coding standard in [standards/](standards). It is binding for all new and changed code:
  - [formatting.md](standards/formatting.md), [naming.md](standards/naming.md), [php.md](standards/php.md)
  - [performance.md](standards/performance.md), [security.md](standards/security.md),
    [html-javascript.md](standards/html-javascript.md), [i18n.md](standards/i18n.md)
  - [testing.md](standards/testing.md), [tooling.md](standards/tooling.md)
  - [versioning.md](standards/versioning.md), [git.md](standards/git.md)
- Key rules: PER Coding Style, `declare(strict_types=1);` and the copyright header in every PHP file, `final` by
  default, fully typed, no `mixed` in own code, enums for every fixed set of values, named arguments, one purpose per
  class, pure logic separated from I/O, validate input at the boundary, escape output by default, bound SQL parameters
  only, no backwards compatibility layers (change APIs directly, consumers migrate with `UPGRADE.md`, see
  [versioning.md](standards/versioning.md), section 4).
- Best possible performance is the first priority, after security and correctness: respond and return early, no
  queries in loops, cache wherever it saves noticeable time (see [performance.md](standards/performance.md)).
- Security and performance improvements are on by default; features that need project data get a required argument
  (`null` as explicit opt-out). Changing a default is breaking (see [versioning.md](standards/versioning.md),
  section 9).
- Explicit comparisons only: always `=== null` / `!== null` (never `is_null()`), never `isset()`, never `empty()`, never
  `==` (see [php.md](standards/php.md), section 5).
- Imperative rules ("do", "never", "always") are mandatory. "Prefer" and "should" mark defaults: deviate only with a
  reason that you can name in the code review.
- Projects may add stricter or project-specific rules in their own `AGENTS.md`, but do not weaken or repeat these
  rules. A project that must deviate says so explicitly in its own `AGENTS.md`, with the reason.
- Leave every file you touch cleaner than you found it, but keep each change focused on one topic. Do not reformat
  unrelated code.
- Run `composer check` (code style, PHPStan, tests) before finishing a task (see [tooling.md](standards/tooling.md));
  it must be green. A PHPStan baseline may only shrink. Fix code style with `composer cs:fix`.
- Without a local PHP of the required version, run PHP and Composer commands through DDEV (`ddev composer check`), if
  the project has a `.ddev/` configuration. If DDEV is not running, ask the user to start it (`ddev start`).
- Do not add Composer or npm packages without asking. Before adopting third-party code, check that its license is
  compatible with the project's license (see [php.md](standards/php.md), section 2); ask when in doubt.

## Working on a task

- Prefer short development cycles: small, focused changes that can be released soon.
- Before changing behaviour, write down (or test) what the existing code does, so no feature gets lost.
- Larger refactorings are done one area at a time. Plans, designs and handover notes live in `docs/plans/<topic>/`
  (`docs/plans/forms/plan.md`), apart from the user docs in `docs/`; follow the plan and append handover notes there.
  A finished plan gets a last handover note and moves to `docs/plans/done/<topic>/` as history; it is not deleted.
- Frontend review: in projects with a frontend developer or designer (the project's `AGENTS.md` says so, without
  naming them), every change of HTML templates or CSS needs their approval before it is released. The handover notes
  of the task (without a plan: the final report) list the changed templates and CSS files under "Frontend review",
  each with a short note what changed; the QA of a plan collects them into one list.
- AI assistants never edit HTML template files, CSS or JavaScript; the frontend developer changes them manually. Write
  each needed change as a task for the frontend developer (what, where, why) and list it under "Frontend review" in
  the handover notes or the final report (see [html-javascript.md](standards/html-javascript.md)).
- Fix problems at their cause: when it lies in a library maintained by Actra (e.g. `actra/yuf`, `actra/backend`:
  bug, missing typed API or feature), add no local workaround (wrapper, cast, copy of library code) in the consuming
  project. Describe the cause and write a complete prompt for the library's own session (goal, the cleanest API
  proposal, tests, `README.md` and a complete `UPGRADE.md` entry). After the library release, the project raises its
  constraint and migrates (see [versioning.md](standards/versioning.md), section 8). Changed HTML output of a library
  in a project is such a cause: the library restores the old output (see [versioning.md](standards/versioning.md),
  section 4); the project adapts no templates, CSS or JavaScript to it.
- Sessions working in parallel never write the same files (`UPGRADE.md`, a plan): they return the text and the
  reviewing session merges it. The reviewing session checks every reported change with `git diff` instead of trusting
  the report.
- Mention assumptions. Ask when a requirement is ambiguous and the answer changes the result.
- Before adding a rule to the `AGENTS.md` or the standards of a project, decide whether it applies to every Actra
  project. If so, add it to this coding standard instead (with a release), and keep only the project-specific part in
  the project.

## Response style

- Be concise. No filler text, no introductory or concluding pleasantries.
- Do not summarize or restate the problem unless asked.
- Mention assumptions when relevant.
- Do not mention the attached context unless it is needed for the answer.
- When code is requested, show only the changed code blocks, each with its file path. Do not repeat unchanged
  surrounding code.
- Explain code with short inline comments in the code rather than long markdown paragraphs.

## Files

- Every file ends with a single newline (see [formatting.md](standards/formatting.md)).
- Keep `README.md` short: what the project is, how to install, configure and use it, links to details. Details go into
  topic files in `docs/` (`docs/forms.md`) or the code; remove what is outdated instead of adding to it. Keep the top
  level clean: no further documentation files there. Keep `UPGRADE.md` entries short (see
  [versioning.md](standards/versioning.md), section 6). Never leave out instructions developers or AI assistants
  need.
- Projects whose `.gitignore` whitelists tracked files: new top-level files or directories must be added there,
  otherwise they are not committed.
- Never commit secrets, credentials, personal data or real customer data (see [security.md](standards/security.md)).

## Git & commits

- Never run `git commit`, `git add` or `git push` on your own. Prepare the commit message and let the user commit.
- Inspect the actual changes (`git status`, `git diff`, `git diff --staged`) before proposing a commit message.
- Commit messages follow [git.md](standards/git.md) (Conventional Commits, short and clean).

## Before commit suggestions

When asked to review changes before commit, inspect the changed files and answer:

1. Read `README.md` and say whether it needs to be updated. Propose short additions only.
2. Read `UPGRADE.md` and say whether it needs to be updated (always for breaking changes), with a short entry. If the
   project has none, say whether adding one would be useful.
3. Suggest a commit message following [git.md](standards/git.md) and the style of previous commit messages.
4. For versioned packages, check the existing Git tags (`git tag --sort=-v:refname`) and suggest the next release tag
   (see [versioning.md](standards/versioning.md)). For a library with a skeleton project, say whether the skeleton
   needs an update (see [versioning.md](standards/versioning.md), section 1).
