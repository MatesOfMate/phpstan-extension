# AGENTS.md

Guidelines for AI agents helping users create Symfony AI Mate extensions from this template.

## Agent Role

When assisting with this repository, you are helping developers **create new MCP extensions** for their frameworks, CMSs, or tools. This is a template repository - users will customize it for their needs.

## Key Responsibilities

### 1. Guide Template Customization
Help users replace placeholder content:
- Replace `Example`/`ExampleExtension` with their framework name
- Update `composer.json` package name to `matesofmate/{framework}-extension`
- Update namespace from `MatesOfMate\ExampleExtension\` to `MatesOfMate\{Framework}Extension\`
- Replace `@your-username` in CODEOWNERS with actual GitHub username

### 2. Tool/Resource Development
Assist with creating MCP capabilities:
- Tools: Executable actions marked with `#[McpTool]`
- Resources: Static context data marked with `#[McpResource]`
- Service registration in `config/services.php`
- Comprehensive tests in `tests/Capability/`

### 3. Quality Assurance
Ensure code meets standards:
- Run `composer lint` before commits
- Run `composer test` to verify functionality
- Check that all examples use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`
- Verify proper file headers are present

### 4. Documentation Support
Help maintain clear documentation:
- Update README.md with framework-specific installation steps
- Document tool capabilities and when AI should use them
- Provide usage examples for end users

## Template-Specific Standards

### Code Style Conventions
- ✅ **No** `declare(strict_types=1)` - Omitted by design
- ✅ **No** `final` classes - Allow extensibility
- ✅ All JSON encoding uses `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`
- ✅ File headers include MatesOfMate copyright

### Tool Implementation Checklist
When creating new tools:
- [ ] Clear, descriptive `#[McpTool]` name: `{framework}-{action}`
- [ ] Helpful description explaining when AI should use it
- [ ] Returns JSON string for structured data
- [ ] Registered in `config/services.php`
- [ ] Has corresponding test in `tests/Capability/`
- [ ] Test validates JSON output structure

### Resource Implementation Checklist
When creating new resources:
- [ ] Custom URI scheme: `{framework}://path`
- [ ] Descriptive name: `{framework}_{name}`
- [ ] Returns array with `uri`, `mimeType`, `text` keys
- [ ] `text` value is JSON string (for JSON mimeType)
- [ ] Registered in `config/services.php`
- [ ] Has corresponding test validating structure

## Workflow Guidelines

### When User Starts New Extension
1. Confirm framework/CMS they're building for
2. Help search/replace all `Example` references
3. Update `composer.json` with correct package name
4. Guide CODEOWNERS update
5. Ensure tests pass: `composer test && composer lint`

### When Adding New Tools
1. Discuss tool purpose and when AI should use it
2. Create class in `src/Capability/`
3. Add `#[McpTool]` attribute with clear description
4. Implement method returning JSON
5. Register in `config/services.php`
6. Create test validating behavior
7. Run quality checks

### When Adding New Resources
1. Identify what static context would be helpful
2. Create class in `src/Capability/`
3. Add `#[McpResource]` attribute with URI and name
4. Implement method returning proper structure
5. Register in `config/services.php`
6. Create test validating return structure
7. Run quality checks

## Development Commands Reference

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run tests with coverage
composer test -- --coverage-html coverage/

# Check all quality tools
composer lint

# Auto-fix code style and refactoring
composer fix

# Individual tools
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer fix --dry-run --diff
vendor/bin/rector process --dry-run
vendor/bin/phpunit tests/Capability/SpecificTest.php
```

## Common Mistakes to Prevent

### ❌ Don't
- Don't add `declare(strict_types=1)` to PHP files
- Don't make classes `final`
- Don't use `json_encode()` without error flags
- Don't forget to register new capabilities in `config/services.php`
- Don't skip tests
- Don't use generic tool descriptions like "A tool for doing things"

### ✅ Do
- Keep classes extensible (non-final)
- Use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT` for JSON encoding
- Write specific, actionable tool descriptions
- Register all capabilities in service container
- Test all tools and resources
- Run `composer lint` before committing

## Communication Style

- **Practical and hands-on** - Show code examples
- **Encouraging** - Building MCP extensions is new for many
- **Specific** - Refer to exact file paths and line numbers
- **Quality-focused** - Remind about running tests and linting

## Before Publishing Checklist

Help users verify before publishing their extension:
- [ ] All `Example`/`ExampleExtension` references replaced
- [ ] `composer.json` has correct package name
- [ ] CODEOWNERS updated with real GitHub username
- [ ] README.md has framework-specific documentation
- [ ] All tools have clear descriptions
- [ ] All tests pass: `composer test`
- [ ] All quality checks pass: `composer lint`
- [ ] LICENSE file updated with correct name/org
- [ ] Tagged release (e.g., `v0.1.0`)
- [ ] Submitted to Packagist

## Commit Message Guidelines

**CRITICAL**: Never include AI attribution in commit messages.

### Format
```
Short descriptive summary

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
- ✅ Focus on WHY and WHAT, not technical details

### Good Examples
```
Add entity relationship discovery

- Enable AI to understand entity associations
- Include bidirectional relationship mapping
- Provide inverse side information
```

```
Improve tool error handling

- Add graceful degradation for missing dependencies
- Provide actionable error messages
- Include recovery suggestions
```

### Bad Examples
```
Update ExampleTool.php

Co-Authored-By: Claude Code <noreply@anthropic.com>
```

```
Add new feature - coded by claude-code
```

### When Creating Commits for Users
Always help users create clean commit messages that:
- Explain what conceptually changed
- Avoid technical file paths or implementation details
- Never include AI attribution or mentions
