# PHPStan Extension for Symfony AI Mate

Token-optimized PHPStan static analysis tools for AI assistants. This extension provides MCP (Model Context Protocol) tools that execute PHPStan analysis and return results in TOON (Token-Oriented Object Notation) format, achieving ~67% token reduction compared to raw PHPStan JSON output.

## Features

- **Static analysis efficiently** - Analyze entire project, specific files, or paths
- **TOON format output** - ~67% token reduction vs. raw PHPStan JSON output using [helgesverre/toon](https://github.com/HelgeSverre/toon-php)
- **Multiple output modes** - Choose between toon, summary, detailed, by-file, or by-type formats
- **Auto-configuration** - Automatically detects `phpstan.neon`, `phpstan.neon.dist`, `phpstan.dist.neon`
- **Fast execution** - Direct Symfony Process integration with current PHP binary
- **Message truncation** - Smart message shortening using common package utilities

> **Note**: Git diff analysis tool (`phpstan-analyse-diff`) will be implemented in a future release.

## Installation

```bash
composer require --dev matesofmate/phpstan-extension
```

The extension is automatically discovered by Symfony AI Mate.

## Available Tools

### `phpstan-analyse`

Run PHPStan static analysis on entire project or specific paths.

**Parameters:**
- `configuration` (optional): Path to phpstan.neon configuration file
- `level` (optional): Analysis level (0-9)
- `path` (optional): Specific path to analyze
- `mode` (optional): Output format - `toon` (default), `summary`, `detailed`, `by-file`, `by-type`

**Examples:**
```php
// Run full analysis
phpstan_analyse()

// Analyze specific path
phpstan_analyse(path: "src/Service")

// Use custom level
phpstan_analyse(level: 8)

// Summary mode for quick overview
phpstan_analyse(mode: "summary")

// Custom configuration
phpstan_analyse(configuration: "phpstan.custom.neon")
```

### `phpstan-analyse-file`

Run PHPStan analysis on a specific file.

**Parameters:**
- `file` (required): Path to the file to analyze
- `configuration` (optional): Path to phpstan.neon configuration file
- `level` (optional): Analysis level (0-9)
- `mode` (optional): Output format (same as phpstan-analyse)

**Examples:**
```php
// Analyze single file
phpstan_analyse_file("src/Service/UserService.php")

// With custom level
phpstan_analyse_file("src/Controller/ApiController.php", level: 9)

// Detailed mode with full messages
phpstan_analyse_file("src/Repository/UserRepository.php", mode: "detailed")
```

### `phpstan-clear-cache`

Clear PHPStan result cache to force fresh analysis.

**Parameters:**
- `configuration` (optional): Path to phpstan.neon configuration file

**Examples:**
```php
// Clear cache with default configuration
phpstan_clear_cache()

// Clear cache for custom configuration
phpstan_clear_cache(configuration: "phpstan.custom.neon")
```

## Output Format (TOON)

TOON (Token-Oriented Object Notation) provides minimal token usage while maintaining readability:

**Successful analysis:**
```
summary:
  level: 6
  files_with_errors: 0
  total_errors: 0
  time: 3.421s

status: OK
```

**Analysis with errors:**
```
summary:
  level: 6
  files_with_errors: 4
  total_errors: 12
  time: 4.892s

errors:
  - file: UserService.php
    line: 45
    message: $id: int expected, string given
    ignorable: true
  - file: UserService.php
    line: 67
    message: getUser(): returns User|null not User
    ignorable: true
  - file: ApiController.php
    line: 23
    message: Access to undefined property
    ignorable: false
```

### Output Modes

The extension supports multiple output modes via the `mode` parameter:

- **`toon` (default)**: Balanced format with full context (file basenames, line numbers, messages)
- **`summary`**: Ultra-compact with just totals and status (~89% token reduction)
- **`detailed`**: Full file paths and complete error messages
- **`by-file`**: Groups errors by file for easier navigation
- **`by-type`**: Groups errors by category (missing-type, nullable-return, undefined-property, etc.)

**Example - Summary mode:**
```
files_with_errors: 4
total_errors: 12
level: 6
status: FAIL
```

**Example - By-type mode:**
```
summary:
  total_errors: 12

by_type:
  missing-type:
    - file: UserService.php
      line: 45
      message: Property $name has no type
  nullable-return:
    - file: Repository.php
      line: 67
      message: Method should return User|null
  undefined-property:
    - file: Controller.php
      line: 23
      message: Access to undefined property
```

### Token Efficiency

Compared to standard PHPStan JSON output:

**JSON (~450 tokens):**
```json
{
  "totals": {
    "errors": 12,
    "file_errors": 4
  },
  "files": {
    "src/Service/UserService.php": {
      "messages": [
        {
          "message": "Parameter $id of method UserService::findById() has no type.",
          "line": 45,
          "ignorable": true
        }
      ]
    }
  }
}
```

**TOON (~150 tokens - 67% reduction):**
```
summary:
  level: 6
  files_with_errors: 4
  total_errors: 12

errors:
  - file: UserService.php
    line: 45
    message: $id: int expected
    ignorable: true
```

## How It Works

### Architecture

The extension uses a layered architecture:

1. **Runner Layer** - Executes PHPStan via Symfony Process (uses current PHP binary)
2. **Parser Layer** - Extracts structured data from PHPStan JSON output
3. **Formatter Layer** - Converts results to TOON format using helgesverre/toon
4. **Tools Layer** - MCP tools with `#[McpTool]` attributes

### Process Flow

```
User Request
    ↓
MCP Tool (AnalyseTool, AnalyseFileTool, etc.)
    ↓
PhpStanRunner (Symfony Process with current PHP binary + PHPStan)
    ↓
PHPStan JSON Output
    ↓
JsonOutputParser (Extract errors, summary, with message truncation)
    ↓
ToonFormatter (Convert to TOON format)
    ↓
Return to AI Assistant
```

### PHP Binary Detection

The extension automatically uses the same PHP binary that's currently running (`PHP_BINARY`), ensuring consistency between your environment and analysis execution.

### Message Truncation

Error messages are intelligently truncated using the shared `MessageTruncator` from the common package:
- Removes common prefixes ("Parameter ", "Method ", "Property ", etc.)
- Shortens fully-qualified class names
- Limits message length while preserving essential information

## Resources

### `phpstan://config`

Provides PHPStan project configuration details in TOON format.

**Returns:**
```
project_root: /path/to/project
config_file: /path/to/project/phpstan.neon.dist
config_exists: true
configured_level: 6
config_content: |
  parameters:
      level: 6
      paths:
          - src
```

## Development

### Quality Commands

```bash
# Run tests
composer test

# Check code quality (PHPStan level 8, PHP CS Fixer, Rector)
composer lint

# Auto-fix code style and apply refactorings
composer fix
```

### Testing

The extension includes comprehensive tests:

- Unit tests for all core components
- Integration tests for tool execution
- PHPStan level 8 compliance
- PHP CS Fixer code style enforcement
- Rector PHP 8.2+ modernization

### CI/CD

GitHub Actions automatically runs on every push and pull request:
- **Lint**: Validates composer.json, runs Rector, PHP CS Fixer, PHPStan
- **Test**: Runs PHPUnit on PHP 8.2 and 8.3

## Requirements

- PHP 8.2 or higher
- PHPStan 2.0 or higher (installed in your project)
- Symfony AI Mate 0.1 or higher

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](../github/CONTRIBUTING.md) for details.

### Commit Message Convention

```
Short summary (50 chars or less)

- Conceptual change description
- Another concept or improvement
```

**Do not include AI attribution** (no "Co-Authored-By: Claude" or similar).

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Resources

- [Symfony AI Mate Documentation](https://symfony.com/doc/current/ai/components/mate.html)
- [PHPStan Documentation](https://phpstan.org/)
- [TOON Format Specification](https://github.com/HelgeSverre/toon-php)
- [MatesOfMate Organization](https://github.com/matesofmate)

---

*Built with the MatesOfMate community*
