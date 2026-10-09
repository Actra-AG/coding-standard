# Actra Coding Standard

Shared coding standard for all PHP projects and libraries of [Actra AG](https://www.actra.ch), for developers and AI
assistants. It builds on PSR and PER Coding Style, adds stricter rules for types, security and testing, and enforces
them with a shared PHPStan (level 10, strict) and PHP-CS-Fixer configuration.

This repository is public: rules only, never project data, credentials, hostnames or customer names.

## Contents

- [AGENTS.md](AGENTS.md): entry point with the working rules and links to all [standards/](standards)
- [config/](config): shared PHPStan and PHP-CS-Fixer configuration
- [templates/](templates): starter files for projects

## Usage

1. Install it (brings PHPStan with extensions and PHP-CS-Fixer):
   ```bash
   composer require --dev actra/coding-standard
   ```
2. Copy the files from [templates/](templates) into the project root and adapt them (project part of
   `AGENTS.md`, license in the header of `.php-cs-fixer.dist.php`).
3. Add the Composer scripts from [tooling.md](standards/tooling.md) and make `composer check` green (PHPStan baseline
   for legacy code). Projects with request and session objects also include
   [phpstan-no-superglobals.neon](config/phpstan-no-superglobals.neon).
4. Remove rules from the project documentation that are already covered here.

Update with `composer update actra/coding-standard` after reading [UPGRADE.md](UPGRADE.md).

## Changing the standard

Pull requests with a short reason per rule; only rules that apply to every Actra project (see
[versioning.md](standards/versioning.md) and [git.md](standards/git.md)).

## License

[MIT](LICENSE)
