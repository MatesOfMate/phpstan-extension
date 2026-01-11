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
vendor/bin/mate discover
```

The extension is automatically enabled by Symfony AI Mate.

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

## Requirements

- PHP 8.2 or higher
- PHPStan 2.0 or higher (installed in your project)
- Symfony AI Mate 0.1 or higher

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](https://github.com/MatesOfMate/.github/blob/main/CONTRIBUTING.md) for details.

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Resources

- [Symfony AI Mate Documentation](https://symfony.com/doc/current/ai/components/mate.html)
- [PHPStan Documentation](https://phpstan.org/)
- [TOON Format Specification](https://github.com/HelgeSverre/toon-php)
- [MatesOfMate Organization](https://github.com/matesofmate)

---

*"Because every Mate needs Mates"*
