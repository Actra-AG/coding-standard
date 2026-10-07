# AGENTS.md

Persistent instructions for developers and AI assistants working in this repository.

## Global standard

This project follows the Actra coding standard, installed as development dependency `actra/coding-standard`
(https://github.com/Actra-AG/coding-standard).

- Read [vendor/actra/coding-standard/AGENTS.md](vendor/actra/coding-standard/AGENTS.md) and the standards linked
  there before working on this project. They are binding. If `vendor/` is missing, run `composer install` first.
- The rules below only **add** project-specific rules or state explicit deviations (with reason). They take precedence
  over the global standard where they conflict.

## Project context

- What the project is (application or library, Composer package name, who uses it).
- Minimum PHP version and how versions are released.
- Ongoing goals (e.g. refactoring of legacy areas).

## Project-specific rules

- Allowed dependencies and tools (if stricter than the global standard).
- Architecture rules, important directories and their purpose.
- How to run and check the application (URL of the DDEV site, example app, …).

## Deviations from the global standard

- None.
