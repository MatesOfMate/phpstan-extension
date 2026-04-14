# PHPStan Extension for Symfony AI Mate

Token-efficient PHPStan analysis tools for AI assistants. This package runs PHPStan and returns encoded structured results geared toward debugging and iteration.

## Features

- analyse the full project or a selected file or directory
- clear PHPStan result cache
- three consistent compact output modes
- automatic configuration detection

## Installation

```bash
composer require --dev matesofmate/phpstan-extension
vendor/bin/mate init
```

In current AI Mate setups, extension discovery is handled automatically after Composer install and update. Run `vendor/bin/mate discover` when you want to refresh discovery artifacts such as `mate/AGENT_INSTRUCTIONS.md`.

Useful Mate commands:

```bash
vendor/bin/mate debug:extensions
vendor/bin/mate debug:capabilities
vendor/bin/mate mcp:tools:list --extension=matesofmate/phpstan-extension
```

Use the generated wrapper for Codex:

```bash
./bin/codex
```

## Custom Command Configuration

If PHPStan must run through Docker or another wrapper command, configure `matesofmate_phpstan.custom_command`.

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()->set('matesofmate_phpstan.custom_command', [
        'docker', 'compose', 'exec', 'php', 'vendor/bin/phpstan',
    ]);
};
```

## Requirements

- PHP 8.2+
- Symfony AI Mate 0.7+ required
- PHPStan 2.x in the target project

## Available Tools

- `phpstan-analyse`
- `phpstan-clear-cache`

This package returns encoded strings through Mate's core `ResponseEncoder`. Install the suggested `helgesverre/toon` package if you want TOON responses; otherwise the same payload falls back to JSON.

## Output Modes

- `default`
- `summary`
- `detailed`

## Development

```bash
composer install
composer test
composer lint
composer fix
```

## License

MIT
