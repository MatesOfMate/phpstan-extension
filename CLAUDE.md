# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHPStan extension for Symfony AI Mate providing AI assistants with efficient static analysis tools. This extension executes PHPStan analysis and returns results in TOON (Token-Oriented Object Notation) format, achieving ~67% token reduction compared to raw PHPStan JSON output.

## Common Commands

### Development Workflow

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run specific test
vendor/bin/phpunit tests/Capability/AnalyseToolTest.php
vendor/bin/phpunit --filter testExecute

# Check code quality (validates composer.json, runs Rector, PHP CS Fixer, PHPStan)
composer lint

# Auto-fix code style and apply automated refactorings
composer fix
```

### Individual Quality Tools

```bash
# PHP CS Fixer (code style)
vendor/bin/php-cs-fixer fix --dry-run --diff  # Check only
vendor/bin/php-cs-fixer fix                   # Apply fixes

# PHPStan (static analysis at level 8)
vendor/bin/phpstan analyse

# Rector (automated refactoring to PHP 8.2)
vendor/bin/rector process --dry-run           # Preview changes
vendor/bin/rector process                     # Apply changes
```

## Architecture

### Component Structure

**MCP Tools** (`src/Capability/`):
- `AnalyseTool` - Run PHPStan analysis on project or specific paths
- `AnalyseFileTool` - Quick validation of single files
- `ClearCacheTool` - Clear PHPStan result cache
- `BuildsPhpstanArguments` (trait) - Shared argument building logic

**MCP Resources** (`src/Capability/`):
- `ConfigResource` - Provides PHPStan configuration information (path, level, content)

**Core Services**:
- `Runner/PhpStanRunner` - Executes PHPStan CLI commands via ProcessExecutor
- `Parser/JsonOutputParser` - Parses PHPStan JSON output into structured AnalysisResult
- `Parser/NeonParser` - Extracts configuration level from .neon files
- `Formatter/ToonFormatter` - Converts results to TOON format with multiple modes
- `Config/ConfigurationDetector` - Auto-detects phpstan.neon/phpstan.neon.dist/phpstan.dist.neon

### Data Flow

```
Tool → PhpStanRunner → ProcessExecutor (common package)
                                ↓
                         PHPStan CLI with --error-format=json
                                ↓
                         JsonOutputParser → AnalysisResult
                                ↓
                         ToonFormatter → TOON output
```

### Output Modes

The ToonFormatter supports five output modes:
- `toon` - Compact format with basename files only (~67% token reduction)
- `summary` - Totals only (files_with_errors, total_errors, level, status)
- `detailed` - Full file paths and complete error messages
- `by-file` - Errors grouped by filename
- `by-type` - Errors categorized by type (missing-type, type-mismatch, undefined-method, etc.)

### Common Package Integration

Uses `matesofmate/common` package for shared functionality:

**ProcessExecutor** - CLI tool execution with PHP binary reuse
- Configured with vendor path: `%mate.root_dir%/vendor/bin/phpstan`
- Default timeout: 300 seconds
- Always uses `--error-format=json --no-progress` for analyse command

**MessageTruncator** - Smart message shortening (200 char limit)
- Preserves common prefixes: "Parameter ", "Method ", "Property ", "Call to ", etc.
- Applied during JSON parsing to reduce token usage

**ConfigurationDetector** - Auto-detects config files in order:
1. phpstan.neon
2. phpstan.neon.dist
3. phpstan.dist.neon

### Service Registration

All services registered in `config/config.php` with:
- Autowiring enabled
- Autoconfiguration enabled (discovers #[McpTool] and #[McpResource] attributes)
- Custom process executor with vendor path injection

## Code Quality Standards

### PHP Requirements
- PHP 8.2+ minimum
- No `declare(strict_types=1)` by convention
- No final classes (extensibility)
- JSON encoding: Always use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`

### Quality Tools Configuration
- **PHPStan**: Level 8, includes phpstan-phpunit extension
- **PHP CS Fixer**: `@Symfony` + `@Symfony:risky` rulesets with ordered class elements
- **Rector**: PHP 8.2, code quality, dead code removal, early return, type declarations
- **PHPUnit**: Version 10.0+

### File Header Template

All PHP files must include:
```php
<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
```

### DocBlock Annotations

**@author annotation**: Required on all class-level DocBlocks:
```php
/**
 * Description of the class.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class YourClass
```

**@internal annotation**: Mark implementation details not for external use:
```php
/**
 * Internal parser for PHPStan JSON output.
 *
 * @internal
 * @author Johannes Wachter <johannes@sulu.io>
 */
class JsonOutputParser
```

Use @internal for:
- Parser, formatter, runner classes
- Helper traits
- Internal DTOs (RunResult, AnalysisResult)
- Classes not intended for extension consumers

## Discovery Mechanism

Symfony AI Mate auto-discovers tools and resources via `composer.json`:

```json
{
    "extra": {
        "ai-mate": {
            "scan-dirs": ["src/Capability"],
            "includes": ["config/config.php"]
        }
    }
}
```

## Testing Philosophy

### Test Structure
- Tests mirror `src/` structure in `tests/`
- Extend `PHPUnit\Framework\TestCase`
- Test method names: `testExecute`, `testFormatToon`, `testParseErrors`, etc.

### Key Testing Areas
- Tool parameter validation (required file parameter, level range 0-9)
- JSON output parsing correctness
- TOON format output validation
- Configuration detection logic
- Error categorization in ToonFormatter

### Integration Testing
- Service registration and dependency injection
- Attribute-based discovery (#[McpTool], #[McpResource])
- Process executor integration with common package

## Common Development Patterns

### Adding New Tools

1. Create tool class in `src/Capability/` with `#[McpTool]` attribute
2. Inject required services via constructor (PhpStanRunner, parsers, formatters)
3. Use `BuildsPhpstanArguments` trait if needed for argument construction
4. Register service in `config/config.php`
5. Add corresponding test in `tests/Capability/`

### Adding New Output Modes

1. Add mode to enum in `#[Schema]` attribute on tool parameters
2. Implement format method in `ToonFormatter` (e.g., `formatCustomMode()`)
3. Add match arm in `ToonFormatter::format()` method
4. Add test case in `ToonFormatterTest`

## Commit Message Convention

Keep commit messages clean without AI attribution.

**Format:**
```
Short summary (50 chars or less)

- Conceptual change description
- Another concept or improvement
```

**Rules:**
- ❌ NO AI attribution (no "Co-Authored-By: Claude", etc.)
- ✅ Short, descriptive summary line
- ✅ Bullet list describing concepts/improvements
- ✅ Focus on the WHY and WHAT
