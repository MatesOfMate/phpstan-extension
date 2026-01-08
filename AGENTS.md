# AGENTS.md

Guidelines for AI agents working on the PHPStan AI Mate extension.

## Agent Role

When working with this repository, you are maintaining and improving a **PHPStan AI Mate extension** that provides AI assistants with efficient static analysis capabilities. This is a production extension using TOON (Token-Optimized Output Notation) format for ~67% token reduction.

## Project Context

**Package**: `matesofmate/phpstan-mate-extension`
**Purpose**: Enable AI assistants to run PHPStan analysis with minimal token consumption
**Key Innovation**: TOON format achieving ~67% token reduction vs standard PHPStan JSON output

### Architecture Layers

1. **MCP Tools Layer** (`src/Capability/`) - 4 tools + 1 resource
2. **Runner Layer** (`src/Runner/`) - PHPStan execution with PHP_BINARY
3. **Parser Layer** (`src/Parser/`) - JSON parsing and config detection
4. **Formatter Layer** (`src/Formatter/`) - TOON format generation
5. **Git Layer** (`src/Git/`) - Diff analysis for changed files
6. **DTOs** (`src/DTO/`) - Readonly immutable data objects

## Key Responsibilities

### 1. Maintain Token Efficiency

The TOON format is the core value proposition:
- **Target**: ≥67% token reduction vs PHPStan JSON
- **Current**: ~67% (toon mode), ~89% (summary mode)
- **Critical**: Any formatter changes must preserve efficiency

**When modifying formatters:**
- Measure token counts before and after
- Test with real PHPStan output (10-100 errors)
- Validate compression ratios
- Update README.md with new metrics

### 2. Ensure PHPStan Level 8 Compliance

This extension must pass PHPStan at maximum strictness:
- All array parameters need `@param array<type>` annotations
- All array returns need `@return array<type>` annotations
- Complex return types may need explicit variables
- Always check `getcwd() !== false` before use

**Common PHPStan fixes:**
```php
// ✅ Good: Explicit type annotation
/**
 * @param array<int, string> $command
 */
public function execute(array $command): ProcessResult

// ✅ Good: Explicit variables for complex returns
$auto = [];
$manual = [];
$complex = [];
// ... populate arrays ...
return ['auto' => $auto, 'manual' => $manual, 'complex' => $complex];

// ✅ Good: getcwd() check
$cwd = getcwd();
if (false === $cwd) {
    throw new \RuntimeException('Unable to determine current working directory');
}
```

### 3. Preserve PHP_BINARY Usage

**CRITICAL**: This is a user requirement - PHPStan must execute using the current PHP process binary.

```php
// ✅ Correct pattern (ProcessExecutor.php)
public function buildPhpStanCommand(string $phpStanScript): array
{
    // Use the current PHP binary to execute PHPStan
    return [\PHP_BINARY, $phpStanScript];
}

// ❌ Wrong - don't execute phpstan directly
return [$phpStanScript, 'analyse'];
```

### 4. Maintain Git Integration

The diff analysis feature is a killer feature for AI workflows:
- Analyze only changed files since git ref
- Auto-detect main/master branch
- Handle empty diffs gracefully
- Filter for PHP files only

**When modifying DiffAnalyser:**
- Test with both main and master branches
- Handle repositories with no commits
- Verify `--diff-filter=ACMR` catches all relevant changes

### 5. Quality Assurance

Before any commit:
```bash
# Must all pass
composer lint
composer test

# Individual checks
vendor/bin/phpstan analyse         # 0 errors at level 8
vendor/bin/php-cs-fixer fix        # Auto-fix code style
vendor/bin/rector process          # Apply refactorings
vendor/bin/phpunit                 # 37 tests, 80 assertions
```

## Development Workflows

### Adding a New Tool

1. **Create tool class** in `src/Capability/`:
```php
#[McpTool(
    name: 'phpstan_new_feature',
    description: 'Clear, specific description of when AI should use this tool'
)]
public function execute(string $param): string
{
    // Implementation
    return json_encode($result, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
}
```

2. **Register in** `config/services.php`:
```php
$services->set(NewFeatureTool::class);
```

3. **Create test** in `tests/Capability/`:
```php
public function testReturnsValidJson(): void
{
    $output = $this->tool->execute('test-param');
    $data = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

    $this->assertArrayHasKey('success', $data);
    $this->assertArrayHasKey('output', $data);
}
```

4. **Run quality checks**:
```bash
composer lint && composer test
```

### Modifying TOON Format

1. **Baseline measurement**:
```bash
# Generate sample PHPStan output
vendor/bin/phpstan analyse --error-format=json > sample.json

# Measure current token count (using token counter tool)
# Record: JSON tokens, TOON tokens, reduction %
```

2. **Make changes** to `ToonFormatter.php`

3. **Validate efficiency**:
```bash
# Re-measure token counts
# Ensure ≥67% reduction maintained
# Update README.md if metrics change
```

4. **Test edge cases**:
- Empty results (0 errors)
- Single error
- Many errors (100+)
- Long file paths
- Long error messages

### Fixing PHPStan Issues

**Pattern 1: Array type annotations**
```php
// Error: "Method has no value type specified in iterable type array"
// Fix: Add @param or @return annotation

/**
 * @param array<int, string> $items
 */
public function process(array $items): void
```

**Pattern 2: Complex return types**
```php
// Error: "should return array{auto: list<ErrorMessage>, ...} but returns non-empty-array"
// Fix: Use explicit variables instead of dynamic array

// ❌ Don't build dynamically
$grouped = [];
foreach ($errors as $error) {
    $grouped[$type][] = $error;
}
return $grouped;

// ✅ Use explicit variables
$auto = [];
$manual = [];
$complex = [];
foreach ($errors as $error) {
    if ($type === 'auto') $auto[] = $error;
    elseif ($type === 'manual') $manual[] = $error;
    else $complex[] = $error;
}
return ['auto' => $auto, 'manual' => $manual, 'complex' => $complex];
```

**Pattern 3: getcwd() false handling**
```php
// Error: "Parameter expects string, string|false given"
// Fix: Check for false before use

$cwd = getcwd();
if (false === $cwd) {
    throw new \RuntimeException('Unable to determine current working directory');
}
return $cwd;
```

### Adding Tests

**Test structure** (mirrors `src/` directory):
```
tests/
├── Capability/      # Tool tests
├── Runner/          # Execution tests
├── Parser/          # Parsing tests
├── Formatter/       # Format tests
├── Git/             # Git integration tests
└── DTO/             # DTO tests
```

**Test naming convention**:
- `testReturnsValidJson` - Check JSON structure
- `testHandlesMissingConfiguration` - Edge case
- `testFormatsToonOutput` - Specific behavior
- `testGroupsByFile` - Grouping logic

**Assertion patterns**:
```php
// JSON validation
$data = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);
$this->assertIsArray($data);
$this->assertArrayHasKey('success', $data);

// Error counting
$this->assertCount(3, $result->errors);
$this->assertSame(3, $result->errorCount);

// String matching
$this->assertStringContainsString('summary{', $output);
$this->assertStringStartsNotWith('Parameter ', $message);

// File paths
$this->assertSame('Service/UserService.php', $result);
```

## Template-Specific Standards

### Code Style Conventions
- ❌ **No** `declare(strict_types=1)` - Omitted by design for compatibility
- ❌ **No** `final` classes - Allow extensibility for users
- ✅ **Always** use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT` with json_encode()
- ✅ **Always** use `PHP_BINARY` for PHPStan execution
- ✅ **Always** include MatesOfMate copyright header

### File Header
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

## Common Mistakes to Prevent

### ❌ Don't
- Don't add `declare(strict_types=1)` to PHP files
- Don't make classes `final`
- Don't use `json_encode()` without `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`
- Don't execute PHPStan directly - always use `PHP_BINARY`
- Don't modify formatter without measuring token efficiency
- Don't skip PHPStan type annotations
- Don't forget to test edge cases (0 errors, missing config, no git)
- Don't commit without running `composer lint && composer test`

### ✅ Do
- Keep classes extensible (non-final)
- Use explicit type annotations for PHPStan Level 8
- Measure token efficiency when modifying formatters
- Test with real PHPStan output
- Handle getcwd() false case
- Use explicit variables for complex return types
- Register all new services in `config/services.php`
- Write tests for all new functionality
- Run quality checks before committing

## Development Commands Reference

```bash
# Install dependencies
composer install

# Run all tests (37 tests, 80 assertions)
composer test

# Run tests with coverage
composer test -- --coverage-html coverage/

# Check all quality tools (must all pass)
composer lint

# Auto-fix code style and refactoring
composer fix

# Individual quality tools
vendor/bin/phpstan analyse                      # Must show 0 errors
vendor/bin/php-cs-fixer fix --dry-run --diff   # Preview style fixes
vendor/bin/php-cs-fixer fix                    # Apply style fixes
vendor/bin/rector process --dry-run            # Preview refactorings
vendor/bin/rector process                      # Apply refactorings
vendor/bin/phpunit tests/Capability/AnalyseToolTest.php  # Single test
```

## Architecture Deep Dive

### Layer Dependencies

```
MCP Tools (Capability)
    ↓ depends on
Runner (PhpStanRunner, ProcessExecutor)
    ↓ depends on
Parser (JsonOutputParser, ConfigurationDetector)
    ↓ depends on
Formatter (ToonFormatter, MessageTruncator, ErrorGrouper)
    ↓ depends on
Git (DiffAnalyser)
    ↓ depends on
DTOs (AnalysisResult, ErrorMessage, ProcessResult)
```

### Data Flow

```
1. Tool receives request
   ↓
2. Runner executes PHPStan with PHP_BINARY
   ↓
3. Parser converts JSON to AnalysisResult DTO
   ↓
4. Formatter generates TOON output
   ↓
5. Tool returns JSON response to AI
```

### Configuration Auto-Detection

```
ConfigurationDetector checks (in order):
1. phpstan.neon
2. phpstan.neon.dist
3. phpstan.dist.neon
4. Returns first found or null
```

## Communication Style

- **Precise and technical** - Reference exact file paths and line numbers
- **Evidence-based** - Show token counts, test results, metrics
- **Quality-focused** - Emphasize PHPStan Level 8 compliance
- **Performance-aware** - Measure token efficiency impacts
- **Git-aware** - Reference commits, branches, diffs

## Before Committing Checklist

- [ ] All tests pass: `composer test` (37 tests, 80 assertions)
- [ ] PHPStan Level 8 passes: `vendor/bin/phpstan analyse` (0 errors)
- [ ] Code style passes: `vendor/bin/php-cs-fixer fix`
- [ ] Rector passes: `vendor/bin/rector process`
- [ ] Token efficiency maintained (if formatter changes)
- [ ] Edge cases tested (empty results, missing config, git scenarios)
- [ ] Commit message follows convention (no AI attribution)

## Commit Message Guidelines

**CRITICAL**: Never include AI attribution in commit messages.

### Format
```
Short descriptive summary (50 chars max)

- Conceptual change or improvement
- Another concept addressed
- Additional improvements made
```

### Rules
- ❌ **NEVER** add "Co-Authored-By: Claude" or similar AI attribution
- ❌ **NEVER** mention "coded by claude-code" or AI assistance
- ✅ Describe CONCEPTS and improvements, not file names
- ✅ Use natural language explaining what changed
- ✅ Keep summary under 50 characters
- ✅ Focus on WHY and WHAT, not implementation details

### Good Examples
```
Improve TOON token efficiency

- Reduce message truncation threshold to 60 chars
- Optimize FQCN shortening algorithm
- Add smart prefix removal for common patterns
```

```
Add PHPStan baseline comparison

- Parse baseline.neon files
- Compare current errors against baseline
- Report only new errors in output
```

### Bad Examples
```
Update ToonFormatter.php and MessageTruncator.php

Co-Authored-By: Claude Code <noreply@anthropic.com>
```

```
Fix bugs in formatter - coded by claude-code
```

## Extension Roadmap

Current status: **MVP Complete** (v0.1.0)

### Implemented (Phase 1)
- ✅ 4 core tools (analyse, analyse_file, analyse_diff, clear_cache)
- ✅ TOON format with ~67% token reduction
- ✅ Git-aware diff analysis
- ✅ Auto-configuration detection
- ✅ Comprehensive tests (37 tests, 80 assertions)
- ✅ PHPStan Level 8 compliance

### Potential Future Features (Phase 2)
- ⏳ Baseline status comparison
- ⏳ Level checking preview
- ⏳ Baseline generation
- ⏳ Error explanation tool

When implementing Phase 2 features:
1. Follow existing architecture patterns
2. Maintain PHPStan Level 8 compliance
3. Preserve token efficiency
4. Add comprehensive tests
5. Update README.md with new capabilities
