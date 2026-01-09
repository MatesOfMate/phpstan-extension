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

use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use Mcp\Capability\Attribute\McpTool;

/**
 * Runs PHPStan static analysis with token-optimized output.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
        private readonly ToonFormatter $formatter,
    ) {
    }

    #[McpTool(
        name: 'phpstan-analyse',
        description: 'Run PHPStan static analysis with token-optimized TOON output. Use for: checking code quality, finding type errors, validating changes. Auto-detects configuration. Returns structured analysis results with error details.',
    )]
    public function execute(
        ?string $configuration = null,
        ?int $level = null,
        ?string $path = null,
        string $outputFormat = 'toon',
    ): string {
        $options = [];
        if (null !== $configuration) {
            $options['configuration'] = $configuration;
        }
        if (null !== $level) {
            $options['level'] = $level;
        }
        if (null !== $path) {
            $options['path'] = $path;
        }

        $result = $this->runner->analyse($options);

        return $this->formatter->format($result, $outputFormat);
    }
}
