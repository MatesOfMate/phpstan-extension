<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Capability;

use MatesOfMate\PhpStanExtension\Runner\PhpStanRunner;
use Mcp\Capability\Attribute\McpTool;

/**
 * Clears PHPStan result cache for fresh analysis.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ClearCacheTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
    ) {
    }

    #[McpTool(
        name: 'phpstan-clear-cache',
        description: 'Clear PHPStan result cache to force fresh analysis. Use for: forcing fresh analysis after stale results, clearing cache after major code changes, resetting analysis state after dependency updates or configuration modifications.',
    )]
    public function execute(
        ?string $configuration = null,
    ): string {
        $args = [];

        if (null !== $configuration) {
            $args[] = '--configuration';
            $args[] = $configuration;
        }

        $this->runner->run('clear-result-cache', $args);

        return 'PHPStan cache cleared successfully.';
    }
}
