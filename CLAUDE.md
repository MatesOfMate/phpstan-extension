# CLAUDE.md

Guidance for working on the PHPStan extension.

## Overview

This package provides PHPStan analysis tools for Symfony AI Mate using Mate's core response encoder.

## Current Mate Workflow

- initialize projects with `vendor/bin/mate init`
- current Mate setups auto-discover extensions after install and update
- `vendor/bin/mate discover` refreshes discovery and generated instruction artifacts
- use `./bin/codex` for Codex sessions
- use `vendor/bin/mate debug:extensions` and `vendor/bin/mate debug:capabilities` to troubleshoot loading problems

## Structure

- `src/Capability/` contains tools and resources
- `src/Runner/` runs PHPStan
- `src/Parser/` parses JSON output
- `src/Formatter/` emits encoded MCP output
- `config/config.php` registers services

## Output Strategy

- This package returns encoded strings through Mate's core `ResponseEncoder`.
- Describe TOON as optional runtime behavior with JSON fallback.

## Service Registration

Use `config/config.php`.

## Commands

```bash
composer install
composer test
composer lint
composer fix
vendor/bin/mate mcp:tools:list --extension=matesofmate/phpstan-extension
```

## Standards

- no `declare(strict_types=1)` by project convention
- non-final classes by project convention
- docs must match actual output modes and tool names
