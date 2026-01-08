# PHPStan Extension for Symfony AI Mate

[![CI](https://github.com/matesofmate/phpstan-mate-extension/workflows/CI/badge.svg)](https://github.com/matesofmate/phpstan-mate-extension/actions)

A [Symfony AI Mate](https://symfony.com/doc/current/ai/components/mate.html) extension that provides AI assistants with efficient PHPStan static analysis capabilities, optimized for minimal token consumption.

## Features

- **Token-Optimized Output**: TOON format achieves ~67% token reduction vs standard PHPStan JSON output
- **Git-Aware Analysis**: Analyze only files changed since a git reference (killer feature for AI workflows)
- **Auto-Configuration**: Automatically detects `phpstan.neon`, `phpstan.neon.dist`, `phpstan.dist.neon`
- **Fast Single File Analysis**: Quick validation during development
- **Cache Management**: Clear PHPStan result cache when needed

## Installation

```bash
composer require --dev matesofmate/phpstan-mate-extension
```

The extension will be automatically discovered by Symfony AI Mate.

## Available Tools

### 1. `phpstan_analyse` - Full Project Analysis

Run PHPStan static analysis with token-optimized TOON output.

**Parameters:**
- `configuration` (optional): Path to phpstan.neon file
- `level` (optional): Analysis level (0-9)
- `path` (optional): Specific path to analyze
- `outputFormat` (optional): Output format - `toon` (default), `summary`, `detailed`, `by-file`, `by-type`

**Example:**
```json
{
  "success": true,
  "output": "summary{level,files_with_errors,total_errors,time}:\n6|4|12|4.892s\n\nerrors[12]{file,line,msg,ignorable}:\nUserService.php|45|$id: int expected, string given|T\nUserService.php|67|getUser(): returns User|null not User|T\n...",
  "errorCount": 12,
  "fileErrorCount": 4,
  "level": 6,
  "executionTime": 4.892,
  "memoryUsage": "128MB"
}
```

### 2. `phpstan_analyse_file` - Single File Analysis

Analyze a specific file. Faster than full analysis when working on individual files.

**Parameters:**
- `file` (required): Path to the file to analyze
- `level` (optional): Analysis level (0-9)
- `configuration` (optional): Path to phpstan.neon file

**Example:**
```json
{
  "success": false,
  "output": "summary{level,files_with_errors,total_errors,time}:\n6|1|2|0.523s\n\nerrors[2]{file,line,msg,ignorable}:\nUserService.php|45|Missing return type|T\nUserService.php|67|Nullable return type|T",
  "errorCount": 2,
  "file": "src/Service/UserService.php",
  "level": 6
}
```

### 3. `phpstan_analyse_diff` - Git Diff Analysis (⭐ Killer Feature)

Run PHPStan only on files changed since a git reference (default: main/master). Ideal for validating current work without analyzing the entire codebase.

**Parameters:**
- `baseRef` (optional): Git reference (default: auto-detects main/master)
- `level` (optional): Analysis level (0-9)
- `configuration` (optional): Path to phpstan.neon file

**Example:**
```json
{
  "success": true,
  "output": "summary{base,changed_files,errors}:\nmain|3|0\n\nchanged_files[3]:\nsrc/Service/NewFeature.php\nsrc/Controller/NewController.php\ntests/NewFeatureTest.php\n\nstatus:OK - No errors found in changed files",
  "errorCount": 0,
  "filesAnalysed": 3,
  "baseRef": "main",
  "changedFiles": ["src/Service/NewFeature.php", "src/Controller/NewController.php", "tests/NewFeatureTest.php"]
}
```

### 4. `phpstan_clear_cache` - Cache Management

Clear PHPStan result cache to force fresh analysis. Use when analysis results seem stale or after major code changes.

**Parameters:**
- `configuration` (optional): Path to phpstan.neon file

**Example:**
```json
{
  "success": true,
  "message": "PHPStan cache cleared successfully"
}
```

## TOON Output Format

TOON (Token-Optimized Output Notation) is a compact format designed to minimize token consumption while preserving essential information.

### Standard TOON Format

```
summary{level,files_with_errors,total_errors,time}:
6|4|12|4.892s

errors[12]{file,line,msg,ignorable}:
UserService.php|45|$id: int expected, string given|T
UserService.php|67|getUser(): returns User|null not User|T
ApiController.php|23|Undefined property $request|F
OrderRepo.php|34|findByStatus() undefined method|T
...
```

### Token Efficiency Comparison

| Output Format | Token Count | Reduction |
|---------------|-------------|-----------|
| PHPStan JSON  | ~450 tokens | Baseline  |
| TOON Format   | ~150 tokens | **67%**   |
| Summary Mode  | ~50 tokens  | **89%**   |

### Output Modes

- **`toon`** (default): Balanced format with errors and file/line info
- **`summary`**: Ultra-compact with just numbers and status
- **`detailed`**: Includes fix hints for each error
- **`by-file`**: Groups errors by file
- **`by-type`**: Groups errors by error type (missing-type, nullable-return, etc.)

## Resources

### `phpstan://config` - PHPStan Configuration

Provides information about the project's PHPStan configuration.

**Returns:**
```json
{
  "project_root": "/path/to/project",
  "config_file": "/path/to/project/phpstan.neon",
  "config_exists": true,
  "configured_level": 6,
  "config_content": "parameters:\n    level: 6\n    paths:\n        - src\n"
}
```

## Use Cases

### For AI Assistants

**Quick validation during development:**
```
AI: I'll validate this file with PHPStan.
Tool: phpstan_analyse_file
Result: 2 errors found - missing return types
```

**Git-aware validation (recommended):**
```
AI: Let me check only the files you've changed.
Tool: phpstan_analyse_diff
Result: Analyzed 3 changed files - all clear!
```

**Full project analysis:**
```
AI: Running full PHPStan analysis on your project.
Tool: phpstan_analyse
Result: Found 12 errors across 4 files
```

### Integration with AI Workflows

The extension is optimized for AI-assisted development:

1. **Fast Feedback**: Git diff analysis provides quick validation of current work
2. **Token Efficient**: TOON format minimizes token usage in AI context windows
3. **Auto-Configuration**: No manual setup required, works out of the box
4. **Smart Truncation**: Long error messages are intelligently shortened

## Architecture

### Components

```
src/
├── Capability/          # MCP tools and resources
│   ├── AnalyseTool.php
│   ├── AnalyseFileTool.php
│   ├── AnalyseDiffTool.php
│   ├── ClearCacheTool.php
│   └── ConfigResource.php
├── Runner/              # PHPStan execution
│   ├── PhpStanRunner.php
│   └── ProcessExecutor.php
├── Parser/              # Output parsing
│   ├── JsonOutputParser.php
│   ├── ConfigurationDetector.php
│   └── NeonParser.php
├── Formatter/           # TOON formatting
│   ├── ToonFormatter.php
│   ├── MessageTruncator.php
│   └── ErrorGrouper.php
├── Git/                 # Git integration
│   └── DiffAnalyser.php
└── DTO/                 # Data transfer objects
    ├── AnalysisResult.php
    ├── ErrorMessage.php
    └── ProcessResult.php
```

### Design Principles

- **Token Efficiency**: All output formats optimized for minimal token consumption
- **Smart Defaults**: Auto-detects configuration, git branch, and analysis scope
- **PHP Binary Usage**: Uses current PHP binary (PHP_BINARY) for consistent execution
- **Layered Architecture**: Clear separation between execution, parsing, and formatting

## Development

### Running Tests

```bash
# Run all tests
composer test

# With coverage
composer test -- --coverage-html coverage/

# Run specific test
vendor/bin/phpunit tests/Runner/PhpStanRunnerTest.php
```

### Code Quality

```bash
# Run all quality checks
composer lint

# Auto-fix code style
composer fix

# Individual tools
vendor/bin/php-cs-fixer fix --dry-run --diff
vendor/bin/phpstan analyse
vendor/bin/rector process --dry-run
```

### Quality Standards

- **PHP 8.2+** required
- **PHPStan Level 8** (maximum strictness)
- **Symfony Code Style** via PHP CS Fixer
- **Rector** for automated refactoring to modern PHP

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (keep commits clean, no AI attribution)
4. Run quality checks (`composer lint && composer test`)
5. Push to your branch
6. Open a Pull Request

### Commit Message Convention

```
Short summary (50 chars or less)

- Conceptual change description
- Another concept or improvement
```

**Do not include AI attribution** (no "Co-Authored-By: Claude" or similar).

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Credits

Built with 🤝 by the [MatesOfMate](https://github.com/matesofmate) community.

## Resources

- [Symfony AI Mate Documentation](https://symfony.com/doc/current/ai/components/mate.html)
- [PHPStan Documentation](https://phpstan.org/)
- [MatesOfMate GitHub](https://github.com/matesofmate)
- [Contributing Guide](https://github.com/matesofmate/.github/blob/main/CONTRIBUTING.md)
