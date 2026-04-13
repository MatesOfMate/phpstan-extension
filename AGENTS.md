# AGENTS.md

Guidelines for agents working on the PHPStan extension.

## Focus

Maintain a package-specific MCP extension for PHPStan workflows. Keep package docs, output descriptions, and troubleshooting guidance aligned with the actual implementation.

## Important Rules

- Register capabilities in `config/config.php`.
- Keep docs aligned with the current Mate workflow and debug commands.
- This package uses Mate's core `ResponseEncoder` for MCP-facing payloads.
- Describe TOON as optional runtime behavior provided by Mate, with JSON fallback.

## When Updating Behavior

1. update capability, runner, parser, formatter, or config code
2. update tests
3. update README and `INSTRUCTIONS.md`
4. run `composer test` and `composer lint`

## Commit Messages

Never include AI attribution. Focus on the conceptual change and user-facing outcome.
