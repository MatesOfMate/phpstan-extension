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

class AnalyseFileTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
        private readonly ToonFormatter $formatter,
    ) {
    }

    #[McpTool(
        name: 'phpstan_analyse_file',
        description: 'Run PHPStan analysis on a specific file. Faster than full analysis when working on individual files. Ideal for quick validation of a single file during development.',
    )]
    public function execute(
        string $file,
        ?int $level = null,
        ?string $configuration = null,
    ): string {
        try {
            if (!file_exists($file)) {
                return json_encode([
                    'success' => false,
                    'error' => "File not found: {$file}",
                    'output' => '',
                ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
            }

            $options = ['path' => $file];
            if (null !== $configuration) {
                $options['configuration'] = $configuration;
            }
            if (null !== $level) {
                $options['level'] = $level;
            }

            $result = $this->runner->analyse($options);

            $output = $this->formatter->format($result, 'toon');

            return json_encode([
                'success' => !$result->hasErrors(),
                'output' => $output,
                'errorCount' => $result->errorCount,
                'file' => $file,
                'level' => $result->level,
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
