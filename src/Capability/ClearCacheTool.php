<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Capability;

use MatesOfMate\PhpStan\Runner\PhpStanRunner;
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
        description: 'Clear PHPStan result cache to force fresh analysis. Use when analysis results seem stale or after major code changes, dependency updates, or configuration modifications.',
    )]
    public function execute(
        ?string $configuration = null,
    ): string {
        $this->runner->clearCache($configuration);

        return 'PHPStan cache cleared successfully';
    }
}
