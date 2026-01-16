# AGENTS.md - Multi-Agent Guidelines

Guidelines for AI agents working on the PHPStan extension for Symfony AI Mate.

## Agent Role

When assisting with this repository, you are helping maintain and extend an **MCP extension** that provides PHPStan static analysis tools for AI assistants.

## Key Responsibilities

### 1. Tool Development
Assist with creating and maintaining MCP capabilities:
- Tools: Executable actions marked with `#[McpTool]`
- Resources: Static context data marked with `#[McpResource]`
- Service registration in `config/config.php`
- Comprehensive tests in `tests/`

### 2. Quality Assurance
Ensure code meets standards:
- Run `composer lint` before commits
- Run `composer test` to verify functionality
- Check that all JSON uses `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`
- Verify proper file headers are present

### 3. Documentation Support
Help maintain clear documentation:
- Update README.md with new tools or features
- Document tool capabilities and when AI should use them
- Provide usage examples for end users

## Multi-Agent Coordination

When multiple agents work on this project simultaneously:

### File Ownership
- Each agent should claim files before modifying them
- Wait for acknowledgment before making changes
- Release files when done

### Communication Pattern
```
Agent A: "Claiming src/Capability/AnalyseTool.php for modification"
Agent B: "Acknowledged, will avoid that file"
Agent A: "Releasing src/Capability/AnalyseTool.php - changes complete"
```

## Code Standards

### Code Style Conventions
- **No** `declare(strict_types=1)` - Omitted by design
- **No** `final` classes - Allow extensibility
- All JSON encoding uses `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`
- File headers include MatesOfMate copyright

### Tool Implementation Checklist
When creating new tools:
- [ ] Clear, descriptive `#[McpTool]` name: `phpstan-{action}`
- [ ] Helpful description explaining when AI should use it
- [ ] Returns TOON-formatted string via ToonFormatter
- [ ] Supports `mode` parameter for output modes
- [ ] Use `BuildsPhpstanArguments` trait if needed
- [ ] Registered in `config/config.php`
- [ ] Has corresponding test in `tests/Capability/`
- [ ] Test validates output structure

### Resource Implementation Checklist
When creating new resources:
- [ ] Custom URI scheme: `phpstan://path`
- [ ] Descriptive name: `phpstan_{name}`
- [ ] Returns array with `uri`, `mimeType`, `text` keys
- [ ] `text` value is JSON string
- [ ] Registered in `config/config.php`
- [ ] Has corresponding test validating structure

## Workflow Guidelines

### When Adding New Tools
1. Discuss tool purpose and when AI should use it
2. Create class in `src/Capability/`
3. Add `#[McpTool]` attribute with clear description
4. Inject PhpStanRunner, parsers, and ToonFormatter
5. Implement method returning TOON format
6. Register in `config/config.php`
7. Create test validating behavior
8. Run quality checks

### When Modifying Parser
1. Add method to `JsonOutputParser`
2. Update `AnalysisResult` if new fields needed
3. Add test to parser tests
4. Update formatter if needed

### When Modifying Output Format
1. Update `ToonFormatter` methods
2. Update `ToonFormatterTest`
3. Document changes in README

### When Adding Output Modes
1. Add mode to enum in `#[Schema]` attribute on tool parameters
2. Implement format method in `ToonFormatter` (e.g., `formatCustomMode()`)
3. Add match arm in `ToonFormatter::format()` method
4. Add test case in `ToonFormatterTest`

## Development Commands Reference

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Check all quality tools
composer lint

# Auto-fix code style and refactoring
composer fix

# Individual tools
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer fix --dry-run --diff
vendor/bin/rector process --dry-run
vendor/bin/phpunit tests/Capability/AnalyseToolTest.php
```

## Common Mistakes to Prevent

### Don't
- Don't add `declare(strict_types=1)` to PHP files
- Don't make classes `final`
- Don't use `json_encode()` without error flags
- Don't forget to register new capabilities in `config/config.php`
- Don't skip tests
- Don't use generic tool descriptions

### Do
- Keep classes extensible (non-final)
- Use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT` for JSON encoding
- Write specific, actionable tool descriptions
- Register all capabilities in service container
- Test all tools and resources
- Run `composer lint` before committing

## Quality Gates

Before submitting changes:
1. `composer lint` passes
2. `composer test` passes
3. New code has tests
4. Documentation updated if needed

## Commit Message Guidelines

**CRITICAL**: Never include AI attribution in commit messages.

### Format
```
Short descriptive summary

- Conceptual change or improvement
- Another concept addressed
```

### Rules
- **NEVER** add "Co-Authored-By: Claude" or similar AI attribution
- **NEVER** mention "coded by claude-code" or AI assistance
- Describe CONCEPTS and improvements, not file names
- Use natural language explaining what changed
- Keep summary under 50 characters
- Focus on WHY and WHAT, not technical details
