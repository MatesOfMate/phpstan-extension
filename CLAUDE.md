# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **PHPStan AI Mate extension** that provides AI assistants with efficient static analysis capabilities optimized for minimal token consumption. The extension uses TOON (Token-Optimized Output Notation) format to achieve ~67% token reduction compared to standard PHPStan JSON output.

**Package**: `matesofmate/phpstan-mate-extension`
**Namespace**: `MatesOfMate\PhpStan`

## Essential Commands

### Development Workflow
```bash
# Install dependencies
composer install

# Run all tests (37 tests, 80 assertions)
composer test

# Run tests with coverage report
composer test -- --coverage-html coverage/

# Check code quality (validates composer.json, runs Rector, PHP CS Fixer, PHPStan Level 8)
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
vendor/bin/rector process --dry-run             # Preview changes
vendor/bin/rector process                       # Apply changes

# PHPUnit (run specific test)
vendor/bin/phpunit tests/Capability/AnalyseToolTest.php
vendor/bin/phpunit --filter testMethodName
```

## Architecture

### Core Concepts

**MCP Tools**: This extension provides 4 tools for PHPStan analysis:
- `phpstan_analyse` - Run full PHPStan analysis with TOON output
- `phpstan_analyse_file` - Analyze single file
- `phpstan_analyse_diff` - Analyze only git-changed files (killer feature)
- `phpstan_clear_cache` - Clear PHPStan result cache

**MCP Resources**: Provides 1 resource:
- `phpstan://config` - PHPStan configuration information

**TOON Format**: Token-Optimized Output Notation achieves ~67% token reduction through:
- Pipe-delimited compact format
- Structured data fields
- Message truncation
- Smart file path shortening

### Directory Structure

```
src/
├── Capability/          # MCP Tools and Resources
│   ├── AnalyseTool.php
│   ├── AnalyseFileTool.php
│   ├── AnalyseDiffTool.php
│   ├── ClearCacheTool.php
│   └── ConfigResource.php
├── Runner/              # PHPStan execution layer
│   ├── PhpStanRunner.php
│   └── ProcessExecutor.php
├── Parser/              # Output parsing and config detection
│   ├── JsonOutputParser.php
│   ├── ConfigurationDetector.php
│   └── NeonParser.php
├── Formatter/           # TOON format generation
│   ├── ToonFormatter.php
│   ├── MessageTruncator.php
│   └── ErrorGrouper.php
├── Git/                 # Git integration for diff analysis
│   └── DiffAnalyser.php
└── DTO/                 # Data transfer objects
    ├── AnalysisResult.php
    ├── ErrorMessage.php
    └── ProcessResult.php

tests/                   # Mirror src/ structure
config/services.php      # Symfony DI configuration
```

### Service Registration

All services are registered in `config/services.php` with autowiring:

```php
$services = $container->services()
    ->defaults()
    ->autowire()      // Auto-inject dependencies
    ->autoconfigure(); // Auto-register MCP attributes

// Tools
$services->set(AnalyseTool::class);
$services->set(AnalyseFileTool::class);
$services->set(AnalyseDiffTool::class);
$services->set(ClearCacheTool::class);

// Resources
$services->set(ConfigResource::class);

// Runner layer
$services->set(PhpStanRunner::class);
$services->set(ProcessExecutor::class);

// Parser layer
$services->set(JsonOutputParser::class);
$services->set(ConfigurationDetector::class);
$services->set(NeonParser::class);

// Formatter layer
$services->set(ToonFormatter::class);
$services->set(MessageTruncator::class);
$services->set(ErrorGrouper::class);

// Git layer
$services->set(DiffAnalyser::class);
```

### Layered Architecture

#### 1. MCP Tools Layer (`src/Capability/`)

**AnalyseTool.php** - Main analysis entry point
```php
#[McpTool(
    name: 'phpstan_analyse',
    description: 'Run PHPStan static analysis with token-optimized TOON output...'
)]
public function execute(
    ?string $configuration = null,
    ?int $level = null,
    ?string $path = null,
    string $outputFormat = 'toon'
): string
```

**AnalyseDiffTool.php** - Git-aware analysis (killer feature)
```php
#[McpTool(
    name: 'phpstan_analyse_diff',
    description: 'Run PHPStan only on files changed since git ref (default: main/master)...'
)]
public function execute(
    ?string $baseRef = null,
    ?int $level = null,
    ?string $configuration = null
): string
```

#### 2. Runner Layer (`src/Runner/`)

**CRITICAL**: Uses `PHP_BINARY` to execute PHPStan for consistent PHP version usage.

```php
// ProcessExecutor.php
public function buildPhpStanCommand(string $phpStanScript): array
{
    // Use the current PHP binary to execute PHPStan
    return [\PHP_BINARY, $phpStanScript];
}
```

**PhpStanRunner.php** coordinates PHPStan execution:
- Builds command arguments (`--error-format=json`, `--no-progress`)
- Tracks execution time and memory usage
- Auto-detects configuration files
- Handles cache clearing

#### 3. Parser Layer (`src/Parser/`)

**JsonOutputParser.php** - Parses PHPStan JSON into `AnalysisResult` DTOs

**ConfigurationDetector.php** - Auto-detects config files:
- `phpstan.neon`
- `phpstan.neon.dist`
- `phpstan.dist.neon`

**NeonParser.php** - Basic NEON parsing for extracting level and settings

#### 4. Formatter Layer (`src/Formatter/`)

**ToonFormatter.php** - Generates 5 output modes:
- `toon` - Token-optimized format (~67% reduction)
- `summary` - Ultra-compact summary (~89% reduction)
- `detailed` - Detailed with fix hints
- `by-file` - Grouped by file
- `by-type` - Grouped by error type

**MessageTruncator.php** - Intelligent message shortening:
- Removes common prefixes
- Shortens FQCNs: `\App\Entity\User` → `App\User`
- Truncates long messages with `...`

**ErrorGrouper.php** - Groups errors by:
- File path
- Error type (missing-type, nullable-return, etc.)
- Fixability (auto, manual, complex)

#### 5. Git Layer (`src/Git/`)

**DiffAnalyser.php** - Git integration:
- Detects changed PHP files since base ref
- Auto-detects main/master branch
- Filters for PHP files only (`--diff-filter=ACMR`)

#### 6. DTOs (`src/DTO/`)

All DTOs use readonly properties for immutability:

```php
readonly class AnalysisResult
{
    public function __construct(
        public int $errorCount,
        public int $fileErrorCount,
        public array $errors, // ErrorMessage[]
        public ?int $level,
        public ?float $executionTime,
        public ?string $memoryUsage,
    ) {}

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }
}
```

## Code Quality Standards

### Important Design Decisions

⚠️ **Template-specific conventions** (maintained for consistency):

- **No strict types declarations** - All PHP files omit `declare(strict_types=1)` by design
- **No final classes** - All classes are non-final to allow extensibility
- **JSON error handling** - Always use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT` with `json_encode()`
- **PHP_BINARY usage** - All PHPStan executions must use current PHP process binary

### PHPStan Level 8 Requirements

**Critical**: This extension must pass PHPStan Level 8 (maximum strictness)

Common type annotations needed:
```php
/**
 * @param array<int, string> $command
 */
public function execute(array $command): ProcessResult

/**
 * @return array<string, mixed>
 */
public function parse(string $json): array

/**
 * @return array{auto: list<ErrorMessage>, manual: list<ErrorMessage>, complex: list<ErrorMessage>}
 */
public function groupByFixability(array $errors): array
```

**getcwd() handling**:
```php
$projectRoot = getcwd();
if (false === $projectRoot) {
    throw new \RuntimeException('Unable to determine current working directory');
}
```

### Testing Conventions

- 37 tests, 80 assertions, 100% passing
- Tests mirror `src/` structure
- Extend `PHPUnit\Framework\TestCase`
- Test names: `testReturnsValidJson`, `testHandlesMissingConfiguration`
- Validate JSON output structure
- Test edge cases (empty results, missing config, git scenarios)

## TOON Output Format

### Format Specification

**Successful Analysis:**
```
summary{level,files,errors,time,memory}:
6|127|0|3.421s|156MB
status:OK
```

**With Errors:**
```
summary{level,files_with_errors,total_errors,time}:
6|4|12|4.892s

errors[12]{file,line,msg,ignorable}:
UserService.php|45|$id: int expected, string given|T
UserService.php|67|getUser(): returns User|null not User|T
ApiController.php|23|Undefined property $request|F
```

### Token Efficiency

| Output Format | Token Count | Reduction |
|---------------|-------------|-----------|
| PHPStan JSON  | ~450 tokens | Baseline  |
| TOON Format   | ~150 tokens | **67%**   |
| Summary Mode  | ~50 tokens  | **89%**   |

## Development Guidelines

### When Adding New Tools

1. Create class in `src/Capability/`
2. Add `#[McpTool]` attribute with clear description
3. Return JSON string: `json_encode($data, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT)`
4. Register in `config/services.php`
5. Create test in `tests/Capability/`
6. Ensure PHPStan Level 8 compliance
7. Run `composer lint && composer test`

### When Modifying Core Layers

**Runner Layer**: Changes must maintain PHP_BINARY usage
**Parser Layer**: Must handle all PHPStan JSON edge cases
**Formatter Layer**: Must preserve token efficiency targets
**Git Layer**: Must handle both main and master branches

### When Fixing PHPStan Issues

Common patterns:
- Add `@param array<type>` for array parameters
- Add `@return array<type>` for array returns
- Use explicit variables instead of dynamic arrays for complex return types
- Always check `getcwd() !== false` before use

## File Header Template

All PHP files must include this copyright header:

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

## Commit Message Convention

**Important**: Keep commit messages clean without AI attribution.

**Format**:
```
Short summary (50 chars or less)

- Conceptual change description
- Another concept or improvement
- More changes as needed
```

**✅ Good Examples**:
```
Improve TOON format token efficiency

- Reduce message truncation threshold
- Optimize file path shortening
- Add smart FQCN abbreviation
```

```
Add support for PHPStan baseline files

- Parse baseline.neon files
- Compare current errors vs baseline
- Report new errors separately
```

**❌ Bad Examples**:
```
Update ToonFormatter.php

Co-Authored-By: Claude Code <noreply@anthropic.com>
```

```
Fix bugs - coded by claude-code
```

**Rules**:
- ❌ NO AI attribution (no "Co-Authored-By: Claude", "coded by claude-code", etc.)
- ✅ Short, descriptive summary line
- ✅ Bullet list describing concepts/improvements, not file names
- ✅ Natural language explaining what changed
- ✅ Focus on the WHY and WHAT, not technical details

## CI/CD

GitHub Actions workflow (`.github/workflows/ci.yml`) runs automatically:
- **Lint job**: Validates composer.json, runs Rector, PHP CS Fixer, PHPStan Level 8
- **Test job**: Runs PHPUnit on PHP 8.2 and 8.3

All checks must pass before merging.
