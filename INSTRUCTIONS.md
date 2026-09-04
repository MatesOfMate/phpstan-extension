## PHPStan Extension

Prefer these Mate tools over raw PHPStan CLI commands when the user is running static analysis.

| User intent | Prefer |
|---|---|
| Analyse the project, a directory, or one file | `phpstan-analyse` |
| Clear PHPStan cache | `phpstan-clear-cache` |

### Guidance

- Use the Mate tools when the user wants analysis results in a compact, structured format.
- Use the `path` parameter on `phpstan-analyse` to target a single file or directory.
- This extension returns encoded structured payloads through Mate's core encoder.
- A `mate-phpstan-static-analysis` skill covers run scoping, error interpretation, and cache handling; consult it before improvising an analysis workflow.
