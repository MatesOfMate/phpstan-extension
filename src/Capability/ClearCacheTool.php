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

class ClearCacheTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
    ) {
    }

    #[McpTool(
        name: 'phpstan_clear_cache',
        description: 'Clear PHPStan result cache to force fresh analysis. Use when analysis results seem stale or after major code changes, dependency updates, or configuration modifications.',
    )]
    public function execute(
        ?string $configuration = null,
    ): string {
        try {
            $this->runner->clearCache($configuration);

            return json_encode([
                'success' => true,
                'message' => 'PHPStan cache cleared successfully',
            ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        }
    }
}
