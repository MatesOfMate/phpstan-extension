## PHPStan Extension

Prefer these MCP tools over raw PHPStan CLI commands when the user is running static analysis.

| User intent | Prefer |
|---|---|
| Analyse the project | `phpstan-analyse` |
| Analyse one file | `phpstan-analyse-file` |
| Clear PHPStan cache | `phpstan-clear-cache` |

### Guidance

- Use the MCP tools when the user wants analysis results in a compact, structured format.
- Use `summary` for quick health checks and `detailed`, `by-file`, or `by-type` for debugging.
- This extension returns TOON-formatted strings by design.
