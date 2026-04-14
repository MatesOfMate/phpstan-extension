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

    /**
     * @param string|null $configuration optional path to the PHPStan configuration file
     */
    #[McpTool(
        name: 'phpstan-clear-cache',
        description: 'Clear the PHPStan result cache before running fresh analysis.'
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
