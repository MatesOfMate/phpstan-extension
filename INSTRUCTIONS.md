## PHPStan Extension

Use MCP tools instead of CLI for static analysis:

| Instead of...                           | Use                     |
|-----------------------------------------|-------------------------|
| `vendor/bin/phpstan analyse`            | `phpstan-analyse`       |
| `vendor/bin/phpstan analyse src/X.php`  | `phpstan-analyse-file`  |
| `vendor/bin/phpstan clear-result-cache` | `phpstan-clear-cache`   |

### Benefits

- Token-optimized TOON output (~67% reduction)
- Errors grouped by file or type

### Output Modes

`toon` (default), `summary`, `detailed`, `by-file`, `by-type`
