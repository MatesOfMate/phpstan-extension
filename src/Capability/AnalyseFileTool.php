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
 * Runs PHPStan analysis on a specific file for quick validation.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseFileTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
        private readonly ToonFormatter $formatter,
    ) {
    }

    #[McpTool(
        name: 'phpstan-analyse-file',
        description: 'Run PHPStan analysis on a specific file. Faster than full analysis when working on individual files. Ideal for quick validation of a single file during development.',
    )]
    public function execute(
        string $file,
        ?int $level = null,
        ?string $configuration = null,
    ): string {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("File not found: {$file}");
        }

        $options = ['path' => $file];
        if (null !== $configuration) {
            $options['configuration'] = $configuration;
        }
        if (null !== $level) {
            $options['level'] = $level;
        }

        $result = $this->runner->analyse($options);

        return $this->formatter->format($result, 'toon');
    }
}
