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

class AnalyseTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
        private readonly ToonFormatter $formatter,
    ) {
    }

    #[McpTool(
        name: 'phpstan_analyse',
        description: 'Run PHPStan static analysis with token-optimized TOON output. Use for: checking code quality, finding type errors, validating changes. Auto-detects configuration. Returns structured analysis results with error details.',
    )]
    public function execute(
        ?string $configuration = null,
        ?int $level = null,
        ?string $path = null,
        string $outputFormat = 'toon',
    ): string {
        try {
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

            $output = $this->formatter->format($result, $outputFormat);

            return json_encode([
                'success' => !$result->hasErrors(),
                'output' => $output,
                'errorCount' => $result->errorCount,
                'fileErrorCount' => $result->fileErrorCount,
                'level' => $result->level,
                'executionTime' => $result->executionTime,
                'memoryUsage' => $result->memoryUsage,
            ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'output' => '',
            ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        }
    }
}
